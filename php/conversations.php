<?php

require_once('ini.php');

// Initialize Globals
$PATH_ARR = null; $METHOD = $_SERVER['REQUEST_METHOD'];
if ($_SERVER['PATH_INFO']) $PATH_ARR = explode('/', $_SERVER['PATH_INFO']);
error_log($METHOD . " " . print_r($PATH_ARR, true));
if ($PATH_ARR) error_log("Path Count: " . count($PATH_ARR));
error_log("METHOD: " . $METHOD);


/*
 * @func  ADD CONVERSATION
 */
if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('@@@@@@@@@@ Creating Conversation @@@@@@@@@@');
    $conv = json_decode(file_get_contents('php://input'), true);
    error_log("Conversation: " . print_r($conv, true));

    // Insert into Conversations Database
    $res = $db->exec("INSERT INTO conversations VALUES('" . $conv['id'] . "','" . $conv['name'] . "','" .
    $conv['img'] . "')");
    if ($res) {
        error_log('Conversation Created');
        header('Content-Type: application/json');
        echo json_encode($conv);
    } else {
        error_log('Conversation Not Created');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

    // Insert into members database
    $sql_member = "INSERT INTO members VALUES('" . $_REQUEST['token'] . "','" . $conv['id'] . "');";
    error_log($sql_member);
    $res = $db->exec($sql_member);
    if ($res) {
        error_log('User Added to Members');
    } else {
        error_log('User Not Added to Members');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/*
 * @func  ADD MESSAGE
 */
} else if ($_SERVER['PATH_INFO'] and basename($_SERVER['PATH_INFO']) == 'messages' and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('@@@@@@@@@@ Adding Message to ' . $_SERVER['PATH_INFO'] . ' @@@@@@@@@@');

    // Get Conversation ID from Path
    $convId = basename(dirname($_SERVER['PATH_INFO']));
    error_log("- ConvId = " . $convId);

    // Get Token from Params
    $token = $_REQUEST['token'];

    // Get Message from Data
    $msg = json_decode(file_get_contents('php://input'), true);

    // Create Query
    $sql_msg = "INSERT INTO messages VALUES('" . 
        $msg['id'] . "', " .
        $msg['ts'] . ", '" .
        $msg['author'] . "', '" .
        $msg['content'] . "', '" .
        $convId . "');";
    error_log($sql_msg);

    // Insert into Messages Database
    $res = $db->exec($sql_msg);
    if ($res) {
        error_log('Message Created');
        header('Content-Type: application/json');
        echo json_encode($msg);
    } else {
        error_log('Message Not Created');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/**
 * @func    REMOVE MESSAGE
 * @desc    Removes Message from Conversation
 * --
 * @method  DELETE
 * @route   /conversations.php/<convId>/messages/<msgId>?token=x
 * @return  Empty on success; Error Message on failure
**/
} else if ($PATH_ARR and count($PATH_ARR) == 4 and $PATH_ARR[2] == 'messages' and $METHOD == 'DELETE') {
    error_log('@@@@@@@@@@ Removing Message @@@@@@@@@@');

    // Get Variables
    $convId = $PATH_ARR[1];
    $msgId = $PATH_ARR[3];
    $token = $_REQUEST['token'];

    // Remove Message From Conversation
    $result = removeMessageFromConv($token, $msgId, $convId);
    if ($result) {
        header('Content-Type: application/json');
        echo "";
    } else {
        error_log('Conversations Not Joined');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

    
/**
 * @func    LIST MESSAGES
 * @desc    List messages from a conversation
 * --
 * @method  GET
 * @route   /conversations.php/<convId>/messages?token=x
 * @return  List of message objects on success; Error string on failure
**/
} else if ($PATH_ARR and $PATH_ARR[2] == 'messages' and $METHOD == 'GET') {
    error_log('@@@@@@@@@@ Listing Messages @@@@@@@@@@');

    // Get Passed Arguments
    $convId = $PATH_ARR[1];
    $token = $_REQUEST['token'];

    // Get Messages and Send Result
    $result = getMessagesFromConv($token, $convId);
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        error_log('Messages Could Not Be Listed');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }


/**
 * @func    LIST CONVERSATIONS
 * @desc    Queries database to get conversations matching query and with user as a member
 * --
 * @return  Returns a list of conversations (each as an object)
**/
} else if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'GET') {
    error_log('@@@@@@@@@@ Listing Conversations @@@@@@@@@@');

    // Get Variables
    $token = $_GET['token'];

    // Initialize SQL Query String
    $sql_convs = "SELECT * FROM conversations WHERE id IN "
        . "(SELECT conversation FROM members WHERE user = '" . $token . "')";

    // Add another extension to the query string dedicated to ...
    // ... searching based on the passed params
    $where = '';
    foreach ($_GET as $key => $val) {
        if ($key == 'token') continue;
        if ($where) $where = $where . " AND $key='$val'";
        else $where = "$ke›y='$val'";
    }

    // Adjust Query String if there are more passed queries
    if ($where) $sql_convs = $sql_convs . ' WHERE ' . $where;
    error_log($sql_convs);

    // Make Request and Parse Response
    $convs = array();
    $res = $db->query($sql_convs);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $conv = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'members' => $row['members'],
            'messages' => array(), // @TODO: FIX THIS
            'img' => $row['img']
            );
            error_log("Listing Conversation: " . $conv['id'] . " " . $conv['name']);
            array_push($convs, $conv);
        }
        header('Content-Type: application/json');
        echo json_encode($convs);
    } else {
        error_log('Conversations Not Listed');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/**
 * @func    JOIN CONVERSATION
 * @desc    Adds user to a conversation
 * --
 * @method  POST
 * @route   /conversations.php/<convId>/members?token=x
 * @data    { user: 'X' }
 * @return  Returns the data back
**/
} else if ($_SERVER['PATH_INFO'] and basename($_SERVER['PATH_INFO']) == 'members' and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('@@@@@@@@@@ Joining Conversation @@@@@@@@@@');

    // Get Variables
    $convId = basename(dirname($_SERVER['PATH_INFO']));
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user'];
    $token = $_REQUEST['token'];

    // Add User to Conversation
    $result = addUserToConversation($token, $userId, $convId);
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        error_log('Conversations Not Joined');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/**
 * @func    LEAVE CONVERSATION
 * @desc    Adds user to a conversation
 * --
 * @method  DELETE
 * @route   /conversations.php/<convId>/members?token=x
 * @return  Returns the data back
**/
} else if ($_SERVER['PATH_INFO'] and basename($_SERVER['PATH_INFO']) == 'members' and $_SERVER['REQUEST_METHOD'] == 'DELETE') {
    error_log('@@@@@@@@@@ LEAVING Conversation @@@@@@@@@@');

    // Get Variables
    $convId = basename(dirname($_SERVER['PATH_INFO']));
    $token = $_REQUEST['token'];

    // Add User to Conversation
    $result = removeUserFromConv($token, $convId);
    if ($result) {
        header('Content-Type: application/json');
        echo "";
    } else {
        error_log('Conversations Not Joined');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }


/**
 * @func    UPDATE CONVERSATION
 * @desc    Update conversation
 * --
 * @method  POST
 * @route   /conversations.php/<convId>?token=x
 * @return  Returns the data back on success; Error message on failure
**/
} elseif ($_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('@@@@@@@@@@ Updating Conversation ' . $conv['id'] . " @@@@@@@@@@");
    error_log("Path: " . basename($_SERVER['PATH_INFO']));
    $convId = basename($_SERVER['PATH_INFO']);
    $conv = json_decode(file_get_contents('php://input'), true);
    $sql = "UPDATE conversations SET name = '" . $conv['name'] . "', img = '" . $conv['img'] . "' WHERE id = '" . $convId . "';";
    //$res = $db->exec("UPDATE conversations VALUES('" . $conv['id'] . "','" . $conv['name'] . "','" .
    error_log($sql);
    $res = $db->exec($sql);
    if ($res) {
        error_log('Conversation Updated');
        header('Content-Type: application/json');
        echo json_encode($conv);
    } else {
        error_log('Conversation Not Updated');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/*
 * @func  LIST MEMBERS
 */
} else if ($_SERVER['PATH_INFO'] and basename($_SERVER['PATH_INFO']) == 'members' and $_SERVER['REQUEST_METHOD'] == 'GET') {
    error_log('@@@@@@@@@@ Listing Messages of ' . $_SERVER['PATH_INFO'] . ' @@@@@@@@@@');

    // Get Conversation ID from Path
    $convId = basename(dirname($_SERVER['PATH_INFO']));
    error_log("- ConvId = " . $convId);

    // Get Members from Helper Function
    $members = get_members($NULL, $convId);
    error_log("Members: " . print_r($members, true));
    
    // Send Content Back
    if ($members) {
        header('Content-Type: application/json');
        echo json_encode($members);
    } else {
        error_log('Members Not Listed');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }


}

require_once('end.php');

?>