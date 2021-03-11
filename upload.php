<?php

require_once __DIR__ . '/inc/init.php';
if (!$tools->checkRole('admin')) {
    print json_encode(
        array(
            'error' => 'Permission denied'
        )
    );
    exit;
}

// Header for JSON result
header('Content-type: application/javascript; Charset=UTF-8');

// Check login
if (!array_key_exists('auth', $_SESSION[$prefix . 'data'])) {
    print json_encode(
        array(
            'error' => 'Not authorized'
        )
    );
    exit;
}

// Check current working directory and file name from browser
require_once __DIR__ . '/api/classes/files.php';
$files = new files($config, $variable, $db, $prefix);
$dir = $files->checkDirectory((object)array(
    'directory' => $variable->get('dir', '')
));
$fileName = $files->filterFilename($variable->get('fileName'));

// Upload
if (!move_uploaded_file($_FILES['file']['tmp_name'], $dir['absPath'] . '/' . $fileName)) {
    print json_encode(
        array(
            'error' => 'File upload failed'
        )
    );
    exit;
}

// Done
print json_encode(
    array(
        'error' => false
    )
);