<?php

require('ini.php');

/*
 * @func  ADD CONVERSATION
 */
if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('Creating Conversation');
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
 * @func  LIST CONVERSATIONS
 */
} else if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'GET') {
    error_log('Listing Conversations');

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

    // Get all conversations using conversation IDs
    $sql_convs = "SELECT * FROM conversations";
    $where = '';
    foreach ($_GET as $key => $val) {
        if ($key == 'token') continue;
        if ($where) $where = $where . " AND $key='$val'";
        else $where = "$key='$val'";
    }

    // Connect the SQL Request
    if ($where) {
        $sql_convs = $sql_convs . ' WHERE ' . $where;
        if ($list) $sql_convs = $sql_convs . ' AND ' . $list;
    } else {
        if ($list) $sql_convs = $sql_convs . ' WHERE ' . $list;
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
    error_log('Updating Conversation ' . $conv['id']);
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
}

require('end.php');

?>