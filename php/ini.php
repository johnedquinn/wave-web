<?php

// Show errors on exit
error_reporting(E_ERROR | E_PARSE);

// manage CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type");

// Open database
$db = new SQLite3('test.db');

// Check that DB is opened
if (!$db) {
    echo $db->lastErrorMsg();
    die;
}

// Create Databases
$res = $db->query('SELECT * FROM users');
if (!$res) {
    $db->exec('CREATE TABLE users (id VARCHAR(32), email VARCHAR(32), name VARCHAR(32), surname VARCHAR(32), password VARCHAR(32), img TEXT)') or die;
    $db->exec('CREATE TABLE conversations (id VARCHAR(32), name VARCHAR(32), img TEXT)') or die;
    $db->exec('CREATE TABLE members (user VARCHAR(32), conversation VARCHAR(32))') or die;
    $db->exec('CREATE TABLE messages (id VARCHAR(32), ts INTEGER, author VARCHAR(32), content TEXT, conversation VARCHAR(32))') or die;
    error_log("Created database successfully");
}

// Error Message
error_log('The Database already exists.');

// Manage Authentication
if (isset($_REQUEST['token'])) {
    $token = $_REQUEST['token'];
    $user = NULL;
    $res = $db->query("SELECT * FROM users WHERE id='$token'"); while ($row = $res->fetchArray()) {
        $user = array(
          'id' => $row['id'],
          'email' => $row['email'],
          'name' => $row['name'],
          'surname' => $row['surname'],
          'img' => $row['img'],
          'token' => $row['id']
    ); }
    if (!$user) { $db->close(); http_response_code(401); exit;
    }
}

?>