<?php

// $files[$hash] = array(
//   'absPath' => $file,
//   'relPath' => $relPath,
//   'fileName' => $fileName,
//   'relPathAndFile' => $relPathAndFile
// );

// Get configured database from plugin configuration
$configJson = file_get_contents(__DIR__ . '/../../config/seo_settings.json');
$configObj = json_decode($configJson); // , JSON_OBJECT_AS_ARRAY);
if ((property_exists($configObj, 'database')) && (array_key_exists($configObj->database, $beyond->db->databases))) {
    $database = $beyond->db->databases[$configObj->database];
} else {
    $database = $beyond->db->defaultDatabase;
}

// Getting files from database
$query = $database->select(
    $beyond->prefix . 'seo_data',
    array(
        '*'
    ),
    array(
        // All
    )
);
if ($query === false) {
    print '<div class="cleanNotFound">';
    print 'Can not query table [' . $beyond->prefix . 'seo_data]';
    print '</div>';
    exit;
}
$count = 0;
while ($row = $query->fetch()) {
    $found = false;

    // Does this file still exists on system?
    foreach ($files as $fileNamePathHash => $fileDetails) {
        if ($fileDetails['relPathAndFile'] === $row['filePathName']) {
            $found = true;
            break;
        }
    }

    if ($found === true) {
        // File exists
        print "<div class='cleanItem'>";
        print 'File found: ' . $row['filePathName'];
        print "</div>";
    } else {
        // File does not exist
        print "<div class='cleanItem'>";
        print 'Delete ophaned data: ' . $row['filePathName'];

        $query = $database->delete(
            $beyond->prefix . 'seo_data',
            array(
                'filePathName = \'' . $database->escape($row['filePathName']) . '\''
            )
        );
        if ($query === false) {
            print ' - Can not delete from table [' . $beyond->prefix . 'seo_data]';
        }

        print "</div>";
    }
    $count += 1;
}
if ($count === 0) {
    print '<div class="cleanNotFound">';
    print "Database is clean<br>";
    print '</div>';
}