<!--
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
半山科技
-->
<?php
session_start();
define('USER_DIR', __DIR__ . '/users');

function sanitize($str) {
    return preg_replace('/[^a-zA-Z0-9_\-]/', '', $str);
}

function userExists($username) {
    return is_dir(USER_DIR . '/' . sanitize($username));
}

function verifyPassword($username, $password) {
    $file = USER_DIR . '/' . sanitize($username) . '/password.txt';
    if (!file_exists($file)) return false;
    $hash = trim(file_get_contents($file));
    return password_verify($password, $hash);
}

function getSecrets($username) {
    $file = USER_DIR . '/' . sanitize($username) . '/secrets.txt';
    if (!file_exists($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $secrets = [];
    foreach ($lines as $line) {
        list($label, $secret) = explode('|', $line, 2);
        $secrets[$label] = $secret;
    }
    return $secrets;
}

function saveSecrets($username, $secrets) {
    $file = USER_DIR . '/' . sanitize($username) . '/secrets.txt';
    $lines = [];
    foreach ($secrets as $label => $secret) {
        $lines[] = $label . '|' . $secret;
    }
    file_put_contents($file, implode("\n", $lines));
}
?>
