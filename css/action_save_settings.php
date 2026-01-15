<?php
session_start();

// 1. Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// 2. Get the new title from the form
$new_title = $_POST['app_title'] ?? 'My Workspace';

// 3. Prepare data
$config_data = [
    'app_title' => $new_title
];

// 4. Create the 'data' folder if it doesn't exist
if (!file_exists('data')) { mkdir('data'); }

// 5. Save the title to data/config.json
file_put_contents('data/config.json', json_encode($config_data, JSON_PRETTY_PRINT));

// 6. Go back to the main page
header("Location: index.php?msg=Settings Saved");
?>