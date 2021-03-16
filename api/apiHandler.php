<?php

/**
 * Handle API calls
 */

header('Content-type: application/json; Charset=UTF-8');
include_once __DIR__ . '/../inc/init.php';

$result = false;

try {

    header('Access-Control-Allow-Origin: ' . $beyond->config->get('base', 'api.accessOrigin'));

    // Get called class and load
    $class = $beyond->variable->get('class');
    $plugin = false;
    if (preg_match('/^([a-zA-Z]+)_([a-zA-Z]+)$/', $class, $matches)) {
        $plugin = $matches[1];
        $class = $matches[2];
        $classObjectName = $plugin . '_' . $class;
        if (!file_exists(__DIR__ . '/../plugins/' . $plugin . '/apiClasses/' . $class . '.php')) {
            throw new Exception('API class file not found [' . $class . '] in plugin [' . $plugin . ']');
        }
        include_once __DIR__ . '/../plugins/' . $plugin . '/apiClasses/' . $class . '.php';
    } else if (preg_match('/^([a-zA-Z]+)$/', $class, $matches)) {
        if (file_exists(__DIR__ . '/classes/' . $class . '.php')) {
            $class = $matches[1];
            $classObjectName = $class;
            include_once __DIR__ . '/classes/' . $class . '.php';
        } else if (file_exists(__DIR__ . '/../config/siteClasses/' . $class . '.php')) {
            $class = $matches[1];
            $classObjectName = $class;
            include_once __DIR__ . '/../config/siteClasses/' . $class . '.php';
        } else {
            throw new Exception('API class file not found [' . $class . ']');
        }
    } else {
        throw new Exception('API class name has wrong format [' . $class . ']');
    }

    // Create class object
    if (!class_exists($classObjectName)) {
        throw new Exception('API class [' . $classObjectName . '] does not exist');
    }
    $classObj = new $classObjectName(
        $beyond->config,
        $beyond->variable,
        $beyond->db,
        $beyond->prefix,
        $beyond->languages,
        $beyond->tools
    );

    // Get function name from browser
    $call = $beyond->variable->get('call');

    // Do not allow to call functions beginning with underscore (like __construc)
    if (preg_match('/^_/', $call)) {
        throw new Exception('You are not allowed to call function [' . $call . ']');
    }

    // Cleanup function name
    $call = trim(preg_replace('/[^a-zA-Z]/', '', $call));
    if ($call === '') {
        throw new Exception('No function call defined');
    }

    // Get called function
    if (!method_exists($classObj, $call)) {
        throw new Exception('API class [' . $class . '] has no function call [' . $call . '] defined');
    }

    // Get called parameter
    $data = json_decode($beyond->variable->get('data'));
    if (!is_object($data)) {
        throw new Exception('API parameter [data] is not a valid JSON object');
    }

    // Call function
    $callResult = $classObj->{$call}($data);

    if (!is_array($callResult)) {
        throw new Exception('API call to function [' . $call . '] of class [' . $class . '] returned wrong result');
    }

    // Handle API call error-Result to ensure it has the syntax of the usual script output
    if (!array_key_exists('error', $callResult)) {
        $callResult['error'] = false;
    }

    // Clear class
    $classObj = null;

} catch (Exception $e) {
    $beyond->exceptionHandler->add($e);

}

// Output result to browser
$exceptionArray = $beyond->exceptionHandler->arr();
if ($exceptionArray !== false) {
    print json_encode(array(
        'error' => $exceptionArray[0]['message']
//        'stack' => json_encode($exceptionArray)
    ));
} else {
    print json_encode(
        $callResult
    );
}
unset($exceptionArray);