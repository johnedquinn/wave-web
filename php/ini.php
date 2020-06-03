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


/**
 * @func    isUserInConv
 * @desc    Check that the user is in a specific conversation
 * --
 * @param   userId    String of calling user token
 * @param   convId    String of conversation ID
 * @return  Boolean whether user is in conversation or not
**/
function isUserInConv ($userId, $convId) {
    // Grab variable from outside file
    global $db;

    // Create Query Message
    $sql = "SELECT * FROM members WHERE conversation = '" . $convId . "' AND user = '" . $userId . "';";

    // Query Database
    $res = $db->query($sql);
    if ($res) {
        return (count($res->fetchArray()) > 0) ? true : false;
    } else {
        return false;
    }

}


/**
 * @func    getMessagesFromConv
 * @desc    Get messages from a conversation
 * --
 * @param   token     Calling User ID
 * @param   msgId     Message ID
 * @param   convId    Conversation ID
 * @return  Returns whether message could be removed or not
**/
function getMessagesFromConv ($token, $convId) {
    // Check Passed Arguments
    if (!$token or !$convId) {
        error_log('getMessagesFromConv: Passed Args Failure');
        return null;
    }

    // Check that Calling User is in Conversation
    if (!isUserInConv($token, $convId)) {
        error_log('User not in Conversation');
        return null;
    }

    // Create Query Message
    $sql_msgs = "SELECT * FROM messages WHERE conversation = '" . $convId . "';";

    // Make Request and Parse Response
    $msgs = array();
    $res = $db->query($sql_msgs);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $msg = array(
            'id' => $row['id'],
            'ts' => $row['ts'],
            'author' => $row['author'],
            'content' => $row['content'], // @TODO: FIX THIS
            'conversation' => $row['conversation']
            );
            array_push($msgs, $msg);
        }
        return $msgs;
    } else {
        error_log('Messages Not Listed');
        return null;
    }

}


/**
 * @func    get_members
 * @desc    Gets the members of a conversation if token checks out
 * --
 * @param   token     String of calling user token
 * @param   convId    String of conversation ID
 * @return  A dictionary where each key is a member ID and the value is a dictionary containing member info
**/
function get_members ($token, $convId) {
    // Create Query Message
    $sql = "SELECT * FROM users WHERE id in "
        . "(SELECT user FROM members WHERE conversation = '" . $convId . "')";
    error_log($sql);

    // Search Database for Members and Create Object
    $members = array();
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $member = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'surname' => $row['surname'],
            'email' => $row['email'],
            'img' => $row['img']
            );
            $members[$member['id']] = $member;
        }
        return $members;
    } else {
        return NULL;
    }
}


/**
 * @func    addUserToConversation
 * @desc    Adds user to a conversation
 * --
 * @param   token     Calling User ID
 * @param   userId    Added User ID
 * @param   convId    Conversation ID
 * @return  Returns whether user could be added or not
**/
function addUserToConversation ($token, $userId, $convId) {
    // Check Passed Arguments
    if (!convId or !userId or !token) {
        error_log('addUserToConv: Passed Args Failure');
        return false;
    }

    // Check that Calling User is a Member Already
    if (!isUserInConv($token, $convId)) {
        error_log('addUserToConv: Calling user not a member');
        return false;
    }

    // Create Query String
    $sql = "INSERT INTO members VALUES('" . $userId . "', '" . $convId . "')";
    error_log($sql);

    // Add User to Members Table
    $res = $db->exec($sql);
    if ($res) {
        error_log('User Joined Conversation');
        return true;
    } else {
        error_log('User Could Not Join Conversation');
        return false;
    }
}


/**
 * @func    removeMessageFromConv
 * @desc    Removes message from a conversation
 * --
 * @param   token     Calling User ID
 * @param   msgId     Message ID
 * @param   convId    Conversation ID
 * @return  Returns whether message could be removed or not
**/
function removeMessageFromConv ($token, $msgId, $convId) {
    // Check Passed Arguments
    if (!$token or !$msgId or !$convId) {
        error_log('removeUserFromConv: Passed Args Failure');
        return false;
    }

    // Create Query String
    $sql = "DELETE FROM messages WHERE conversation = '" . $convId . "' AND author = '" . $token . "' AND id = '" . $msgId . "';";
    error_log($sql);

    // Remove Message from Messages Table
    $res = $db->exec($sql);
    if ($res) {
        error_log('Message Deleted');
        return true;
    } else {
        error_log('Message Could Not Be Deleted');
        return false;
    }

}


/**
 * @func    removeUserFromConversation
 * @desc    Removes user from a conversation
 * --
 * @param   token     Calling User ID
 * @param   convId    Conversation ID
 * @return  Returns whether user could be removed or not
**/
function removeUserFromConv ($token, $convId) {
    // Check Passed Arguments
    if (!$convId or !$token) {
        error_log('removeUserFromConv: Passed Args Failure');
        return false;
    }

    // Check that Calling User is a Member Already
    if (!isUserInConv($token, $convId)) {
        error_log('removeUserFromConv: Calling user not a member');
        return false;
    }

    // Create Query String
    $sql = "DELETE FROM members WHERE conversation = '" . $convId . "' AND user = '" . $token . "';";
    error_log($sql);

    // Remove User from Members Table
    $res = $db->exec($sql);
    if ($res) {
        error_log('User Left Conversation');
        return true;
    } else {
        error_log('User Could Not Leave Conversation');
        return false;
    }

}


?>