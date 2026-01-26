<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '', $user);
$config_file = 'data/config_' . $safe_filename . '.json';

$new_title = $_POST['app_title'] ?? 'My Workspace';
$new_footer= $_POST['footer_text'] ?? '© 2026 Data Storage Online';
$new_theme = $_POST['theme'] ?? 'light';
$new_lang  = $_POST['lang'] ?? 'en';

$config_data = [
    'app_title' => $new_title,
    'footer_text' => $new_footer,
    'theme' => $new_theme,
    'lang' => $new_lang
];

if (!file_exists('data')) { mkdir('data'); }
file_put_contents($config_file, json_encode($config_data, JSON_PRETTY_PRINT));

header("Location: index.php?msg=Settings Saved");
?>