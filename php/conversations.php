<?php

require_once('ini.php');

/**
 * @func    get_members_from_IDs
 * @desc    Gets the members of a conversation if token checks out
 * --
 * @param   memberIDs    List of string IDs
 * @return  A dictionary where each key is a member ID and the value is a dictionary containing member info
**/
function get_members_from_IDs ($memberIDs) {
    global $db;

    // Check Passed Args
    if (!$memberIDs || count($memberIDs) == 0) return NULL;

    // Create Query Message
    $sql = "SELECT * FROM users WHERE id = '" . $memberIDs[0] . "'";

    // Piece together the Query String
    foreach ($memberIDs as $mid) {
        if ($mid == $memberIDs[0]) continue;
        $sql = $sql . " OR id = '" . $mid . "'";
    }

    // Search Database for Members and Create Object
    $members = array();
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $member = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'surname' => $row['surname'],
            'email' => $row['email'], // @TODO: FIX THIS
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
 * @func  get_members
 * @desc  Gets the members of a conversation if token checks out
**/
function get_members ($token, $convId) {
    global $db;

    // Create Query Message
    $sql_members = "SELECT * FROM members WHERE conversation = '" . $convId . "';";

    // Get Member IDs of Conversation
    $memberIDs = array();
    $res = $db->query($sql_members);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $mid = $row['user'];
            array_push($memberIDs, $mid);
        }
    } else {
        return NULL;
    }

    // Get the dictionary of members
    $members = get_members_from_IDs($memberIDs);
    return $members;

}

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
 * @func  ADD MESSAGES
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

    
/*
 * @func  LIST MESSAGES
 */
} else if ($_SERVER['PATH_INFO'] and basename($_SERVER['PATH_INFO']) == 'messages' and $_SERVER['REQUEST_METHOD'] == 'GET') {
    error_log('@@@@@@@@@@ Listing Messages of ' . $_SERVER['PATH_INFO'] . ' @@@@@@@@@@');

    // Get Conversation ID from Path
    $convId = basename(dirname($_SERVER['PATH_INFO']));
    error_log("- ConvId = " . $convId);

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
        header('Content-Type: application/json');
        echo json_encode($msgs);
    } else {
        error_log('Messages Not Listed');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }


/*
 * @func  LIST CONVERSATIONS
 */
} else if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'GET') {
    error_log('@@@@@@@@@@ Listing Conversations @@@@@@@@@@');

    // Get all conversation IDs where caller is a member
    $sql_members = "SELECT * from members WHERE user = '" . $_GET['token'] . "';";
    error_log($sql_members);
    $res = $db->query($sql_members);
    $convs = array();
    if ($res) {
        while ($row = $res->fetchArray()) {
            $convId = $row['conversation'];
            error_log("Adding " . $convId . " to convs list");
            array_push($convs, $convId);
        }
    } else {
        error_log('Conversations Not Listed');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

    // Add an extension to our query string dedicated to only using ...
    // ... conversations that the user is part of
    $list = '';
    if (count($convs) == 0) {
        // continue;
    } else if (count($convs) == 1) {
        $list = "id = '" . $convs[0] . "'";
    } else {
        $list = "id = '" . $convs[0] . "'";
        foreach ($convs as $convId) {
            $list = $list . " OR id = '" . $convId . "'";
        }
    }
    error_log("LIST: " . $list);

    // Add another extension to the query string dedicated to ...
    // ... searching based on the passed params
    $where = '';
    foreach ($_GET as $key => $val) {
        if ($key == 'token') continue;
        if ($where) $where = $where . " AND $key='$val'";
        else $where = "$key='$val'";
    }

    // Piece the SQL Request together
    $sql_convs = "SELECT * FROM conversations";
    if ($where) {
        $sql_convs = $sql_convs . ' WHERE ' . $where;
        if ($list && !$_REQUEST['id']) $sql_convs = $sql_convs . ' AND ' . $list;
    } else {
        if ($list && !$_REQUEST['id']) $sql_convs = $sql_convs . ' WHERE ' . $list;
    }
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

/*
 * @func  UPDATE CONVERSATION
 */
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

    // Create Query Message
    /*$sql_members = "SELECT * FROM members WHERE conversation = '" . $convId . "';";

    // Make Request and Parse Response
    $members = array();
    $res = $db->query($sql_members);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $member = $row['user'];
            array_push($members, $member);
            $members[$member] = $member;
        }
        header('Content-Type: application/json');
        echo json_encode($msgs);
    } else {
        error_log('Messages Not Listed');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }
    */

    $members = get_members($NULL, $convId);
    error_log("Members: " . print_r($members, true));
    
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