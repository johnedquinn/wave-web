<?php

require('ini.php');

/*
 * @func  ADD CONVERSATION
 */
if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('Creating Conversation');
    $conv = json_decode(file_get_contents('php://input'), true);
    $res = $db->exec("INSERT INTO conversations VALUES('" . $conv['id'] . "','" . $conv['name'] . "','" .
    $conv['img'] . "')");
    if ($res) {
        error_log('Conversation Created');
        header('Content-Type: application/json'); echo json_encode($conv);
    } else {
        error_log('Conversation Not Created');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/*
 * @func  LIST CONVERSATIONS
 */
} else if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT * FROM conversations";
    /*$where = '';
    foreach ($_GET as $key => $val) {
        if ($key == 'token') continue;
        if ($where) $where = $where . " AND $key='$val'";
        else $where = "$key='$val'";
    }

    if ($where) $sql = $sql . ' WHERE ' . $where;*/
    $convs = array();
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $conv = array(
            'id' => $row['id'],
            'name' => $row['name'],
            'members' => $row['members'],
            'messages' => array(), // @TODO: FIX THIS
            'img' => $row['img']
            );
            array_push($convs, $conv);
        }
        header('Content-Type: application/json');
        echo json_encode($convs);
    } else {
        http_response_code(500);
        echo $db->lastErrorMsg();
    }
/*
 * @func  UPDATE USER
 */
} elseif ($_SERVER['REQUEST_METHOD'] == 'PUT') {

}

require('end.php');

?>