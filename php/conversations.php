<?php

require_once('ini.php');

// Initialize Globals
$PATH_ARR = null; $METHOD = $_SERVER['REQUEST_METHOD'];
if ($_SERVER['PATH_INFO']) $PATH_ARR = explode('/', $_SERVER['PATH_INFO']);


/**
 * @func    ADD CONVERSATION
 * @desc    Create New Conversation
 * --
 * @method  POST
 * @route   /conversations.php?token=x
 * @data    { conv }
 * @return  Conversation object on success; Error Message on failure
**/
if (!$PATH_ARR and $METHOD == 'POST') {
    error_log('@@@@@@@@@@ Creating Conversation @@@@@@@@@@');

    // Get Variables
    $data = json_decode(file_get_contents('php://input'), true);
    $convId = $data['id'];
    $convName = $data['name'];
    $convImg = $data['img'];

    // Insert into Databases
    $result = addConv($token, $convId, $convName, $convImg);

    // Send Response back
    if ($result) {
        error_log('Conversation Created');
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        error_log('Conversation Not Created');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }


/**
 * @func    ADD MESSAGE
 * @desc    Add message to conversation
 * --
 * @method  POST
 * @route   /conversations.php/<convId>/messages?token=x
 * @data    { message }
 * @return  Message object on success; Error Message on failure
**/
} else if ($PATH_ARR and count($PATH_ARR) == 3 and $PATH_ARR[2] == 'messages' and $METHOD == 'POST') {
    error_log('@@@@@@@@@@ Adding Message @@@@@@@@@@');

    // Get Variables
    $convId = $PATH_ARR[1];
    $token = $_REQUEST['token'];
    $data = json_decode(file_get_contents('php://input'), true);
    $msgId = $data['id'];
    $msgTs = $data['ts'];
    $msgAuthor = $data['author'];
    $msgContent = $data['content'];

    // Add to Database
    $result = addMsgToConv($token, $msgId, $msgTs, $msgAuthor, $msgContent, $convId);

    // Send Response Back
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        error_log('Conversations Not Joined');
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
} else if ($PATH_ARR and count($PATH_ARR) == 3 and $PATH_ARR[2] == 'messages' and $METHOD == 'GET') {
    error_log('@@@@@@@@@@ Listing Messages @@@@@@@@@@');

    // Get Passed Arguments
    $convId = $PATH_ARR[1];
    $token = $_REQUEST['token'];

    // Get Messages
    $result = getMessagesFromConv($token, $convId);

    // Send Response
    if (is_array($result)) {
        error_log('Messages Could Be Listed');
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        error_log('Messages Could Not Be Listed');
        http_response_code(500);
        echo "Messages Could Not Be Listed";
    }


/**
 * @func    LIST CONVERSATIONS
 * @desc    Queries database to get conversations matching query and with user as a member
 * --
 * @method  GET
 * @route   /conversations.php?token=x&id=x&..
 * @return  Returns a list of conversations (each as an object)
**/
} else if (!$PATH_ARR and $METHOD == 'GET') {
    error_log('@@@@@@@@@@ Listing Conversations @@@@@@@@@@');

    // Get Variables
    $token = $_GET['token'];
    $query = $_GET;

    // Get Conversations
    $result = getConvs($token, $query);

    // Send Response
    if (is_array($result)) {
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        error_log('Conversations Could Not Be Listed');
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
} else if ($PATH_ARR and count($PATH_ARR) == 3 and $PATH_ARR[2] == 'members' and $METHOD == 'POST') {
    error_log('@@@@@@@@@@ Joining Conversation @@@@@@@@@@');

    // Get Variables
    $convId = basename(dirname($_SERVER['PATH_INFO']));
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user'];
    $token = $_REQUEST['token'];

    // Add User to Conversation
    $result = addUserToConversation($token, $userId, $convId);

    // Send Response Back
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
} else if ($PATH_ARR and count($PATH_ARR) == 3 and $PATH_ARR[2] == 'members' and $METHOD == 'DELETE') {
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
} elseif ($PATH_ARR and count($PATH_ARR) == 2 and $METHOD == 'POST') {
    error_log('@@@@@@@@@@ Updating Conversation ' . $conv['id'] . " @@@@@@@@@@");

    // Get Variables
    $data = json_decode(file_get_contents('php://input'), true);
    $convId = $PATH_ARR[1];
    $convName = $data['name'];
    $convImg = $data['img'];

    // Get Members from Helper Function
    $result = updateConv($token, $convId, $convName, $convImg);
    
    // Send Content Back
    if ($result) {
        error_log('Conversation Updated');
        header('Content-Type: application/json');
        echo json_encode($result);
    } else {
        error_log('Conversation Not Updated');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }


/**
 * @func    LIST MEMBERS
 * @desc    Lists all members in a conversation
 * --
 * @method  GET
 * @route   /conversations.php/<convId>/members?token=x
 * @return  Returns a list of members on success; Error message on failure
**/
} else if ($PATH_ARR and count($PATH_ARR) == 3 and $PATH_ARR[2] == 'members' and $METHOD == 'GET') {
    error_log('@@@@@@@@@@ Listing Members @@@@@@@@@@');

    // Get Conversation ID from Path
    $token = $_REQUEST['token'];
    $convId = $PATH_ARR[1];

    // Get Members from Helper Function
    $members = get_members($token, $convId);
    
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