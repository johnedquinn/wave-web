<?php

require_once('ini.php');

// Initialize Globals
$PATH_ARR = null; $METHOD = $_SERVER['REQUEST_METHOD'];
if ($_SERVER['PATH_INFO']) $PATH_ARR = explode('/', $_SERVER['PATH_INFO']);
error_log(print_r($PATH_ARR, true));

/*
 * @func  ADD USER
 */
if (!$PATH_ARR and $METHOD == 'POST') {
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
} else if ($PATH_ARR and count($PATH_ARR) == 2 and $PATH_ARR[1] == 'login' and $METHOD == 'POST') {
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
        echo $db->lastErrorMsg();
    }

/*
 * @func  LIST USERS
 */
} else if (!$PATH_ARR and $METHOD == 'GET') {
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


/**
 * @func    UPDATE USER
 * @desc    Update User Information
 * --
 * @method  PUT
 * @route   /users.php/<userId>?token=x
 * @data    { user }
 * @return  User object on success; Error Message on failure
**/
} else if ($PATH_ARR and count($PATH_ARR) == 2 and $METHOD == 'PUT') {
    error_log('@@@@@@@@@@ Updating User @@@@@@@@@@');

    // Get Variables
    $token = $_REQUEST['token'];
    $data = json_decode(file_get_contents('php://input'), true);
    $usrId = $PATH_ARR[1];
    $usrName = $data['name'];
    $usrSurname = $data['surname'];
    $usrEmail = $data['email'];
    $usrPass = $data['password'];
    $usrImg = $data['img'];

    // Update User
    $result = updateUser($token, $usrId, $usrName, $usrSurname, $usrEmail, $usrPass, $usrImg);

    // Send Response Back
    if ($result) {
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        error_log('Conversations Not Joined');
        http_response_code(500);
        echo $db->lastErrorMsg();
    }
}

require_once('end.php');

?>