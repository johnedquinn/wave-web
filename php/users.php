<?php

require('ini.php');

/*
 * @func  ADD USER
 */
if (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'POST') {
    // addUser
    error_log('@@@@@@@@@@ Creating User @@@@@@@@@@');
    $user = json_decode(file_get_contents('php://input'), true);
    $res = $db->exec("INSERT INTO users VALUES('" . $user['id'] . "','" . $user['email'] . "','" . $user['name'] . "','" .
    $user['surname'] . "','" . $user['password'] . "','" . $user['img'] . "')");
    if ($res) {
        error_log('User Created');
        header('Content-Type: application/json'); echo json_encode($user);
    } else {
        error_log('User Not Created');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }

/*
 * @func  LOGIN
 */
} elseif ($_SERVER['PATH_INFO'] == '/login' and $_SERVER['REQUEST_METHOD'] == 'POST') {
    error_log('@@@@@@@@@@ Logging in User @@@@@@@@@@');
    $credentials = json_decode(file_get_contents('php://input'), true);
    $email = $credentials['email'];
    $password = $credentials['password'];
    $user = NULL;
    $res = $db->query("SELECT * FROM users WHERE email='$email' AND password='$password'");
    while ($row = $res->fetchArray()) {
        $user = array(
        'id' => $row['id'],
        'email' => $row['email'],
        'name' => $row['name'],
        'surname' => $row['surname'],
        'img' => $row['img'],
        'token' => $row['id']
        );
    }
    if ($user) {
        header('Content-Type: application/json');
        echo json_encode($user);
    } else {
        http_response_code(404);
    }

/*
 * @func  LIST USERS
 */
} elseif (!$_SERVER['PATH_INFO'] and $_SERVER['REQUEST_METHOD'] == 'GET') {
    error_log('@@@@@@@@@@ Listing Users @@@@@@@@@@');
    $sql = "SELECT * FROM users";
    $where = '';
    foreach ($_GET as $key => $val) {
        if ($key == 'token') continue;
        if ($where) $where = $where . " AND $key='$val'";
        else $where = "$key='$val'";
    }

    if ($where) $sql = $sql . ' WHERE ' . $where;
    $users = array();
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetchArray()) {
            $user = array(
            'id' => $row['id'],
            'email' => $row['email'],
            'name' => $row['name'],
            'surname' => $row['surname'],
            'img' => $row['img']
            );
            array_push($users, $user);
        }
        header('Content-Type: application/json');
        echo json_encode($users);
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