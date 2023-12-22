<?php

require 'flight/Flight.php';
require 'DatabaseManager.php';

// 注册 DatabaseManager 实例
Flight::register('db', 'DatabaseManager');
$db = Flight::db();

Flight::route('/', function () {
    global $db;
    $data = $db->getAll('books');
    Flight::json($data);
});

Flight::route('POST /login', function() {
    global $db;
    $requestData = Flight::request()->data;

    $username = $requestData['username'];
    $password = $requestData['password'];

    // 检查用户名是否存在
    $user = $db->getByUsername('users', $username);

    if (!$user) {
        // 用户名不存在
        Flight::json(array('message' => 'Username does not exist'), 404);
        return;
    }

    // 校验密码是否正确
    if (password_verify($password, $user['password'])) {
        // 密码正确，返回登录用户的信息
        Flight::json($user, 200);
    } else {
        // 密码不正确
        Flight::json(array('message' => 'Incorrect password'), 401);
    }
});

Flight::start();