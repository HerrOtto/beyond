<?php

require_once __DIR__ . '/inc/init.php';
if (!$beyond->tools->checkRole('admin')) {
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
if (!array_key_exists('auth', $_SESSION[$beyond->prefix . 'data'])) {
    print json_encode(
        array(
            'error' => 'Not authorized'
        )
    );
    exit;
}

// Check current working directory and file name from browser
$dir = $beyond->tools->checkDirectory($beyond->variable->get('dir', ''));
$fileName = $beyond->tools->filterFilename($beyond->variable->get('fileName'));

// Upload
if (!move_uploaded_file($_FILES['file']['tmp_name'], $dir['absPath'] . '/' . $fileName)) {
    print json_encode(
        array(
            'error' => 'File upload failed'
        )
    );
    exit;
}

unset($dir);
unset($fileName);

// Done
print json_encode(
    array(
        'error' => false
    )
);