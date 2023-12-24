<?php
require 'flight/Flight.php';
require 'DatabaseManager.php';
require 'Utils.php';

// 注册 DatabaseManager 实例
Flight::register('db', 'DatabaseManager');
Flight::register('utils', 'Utils');
$db = Flight::db();
$utils = Flight::utils();

Flight::route('/', function () {
    global $db;
    $data = $db->getAll('user');
    Flight::json($data);
});

//登录
Flight::route('POST /api/login', function() {
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $username = $requestData['username'];
    $password = $requestData['password'];
    $userdata = $db->getBy('user',"username",$username);
    if(!$userdata){
        $res = $utils->response(500,null,'用户不存在');
    }else{
        if($userdata['password']!== $password){
            $res = $utils->response(500,null,'密码错误');
        }else{
            $res = $utils->response(0,$userdata,'');
        }
    }
    Flight::json($res);
});

//注册
Flight::route('POST /api/register', function (){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $username = $requestData['username'];
    $password = $requestData['password'];
    $roleId = $requestData['roleId'];
    $userdata = $db->getBy('user',"username",$username);
    if($userdata){
        $res = $utils->response(500,null,'用户已存在');
    }else{
        $newUser = array(
            'username'=>$username,
            'password'=>$password,
            'roleId'=>$roleId
        );
        $newData = $db->insert('user',$newUser);
        $res = $utils->response(0,$newData,'注册成功');
    }
    Flight::json($res);
});

/* 普通用户 */

// 获取医生列表
Flight::route('GET /api/doctor',function (){
    global $db;
    global $utils;
    $res = $db->getAllBy('user','roleId',1);
    Flight::json($utils->response(0,$res,'查询成功'));
});

// 提交问诊记录
Flight::route('POST /api/inquiry',function (){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $userId = $requestData['userId'];
    $type = $requestData['type'];
    $doctorId = $requestData['doctorId'];
    $memo = $requestData['memo'];
    $advice = $requestData['advice'];
    $createTime = date('Y-m-d H:i:s');
    $newConsultation = array(
        'userId'=>$userId,
        'type'=>$type,
        'doctorId' => $doctorId,
        'memo' => $memo,
        'status' => 0,
        'advice' => $advice,
        'createTime' => $createTime,
    );
    $res = $db->insert('consultation',$newConsultation);
    Flight::json($utils->response(0,$res,'提交成功'));
});

// 获取个人问诊记录 分页
Flight::route('POST /api/getSelfCon',function (){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $page=$requestData["page"];
    $limit=$requestData["limit"];
    $conditions = array(
        "memo" => $requestData["memo"],
        "userId" => $requestData["userId"]
    );
    $res = $db->getAllByPage('consultation', $page, $limit, $conditions,"memo");
    Flight::json($utils->response(0,$res,'操作成功！'));
});


/* 医生 */
// 医生提交问诊记录
Flight::route('POST /api/advise/@id', function ($id){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $advice = $requestData["advice"];
    $doctorId = $requestData["doctorId"];
    $status = 1;
    $adviceTime = date('Y-m-d H:i:s');
    $putData = array(
        "advice"=>$advice,
        "status"=>$status,
        "doctorId"=>$doctorId,
        "adviceTime"=>$adviceTime
        );
    $res = $db->update('consultation',$id,$putData);
    Flight::json($utils->response(0,$res,'诊断成功'));
});


/*超级管理员*/

// 分页查询用户数据
Flight::route('POST /api/selectUsers', function (){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $page=$requestData["page"];
    $limit=$requestData["limit"];
    $conditions = array(
        "username" => $requestData["username"]
    );
    $res = $db->getAllByPage('user', $page, $limit, $conditions,"username");
    Flight::json($utils->response(0,$res,'操作成功！'));
});

// 根据用户id删除用户
Flight::route('POST /api/deleteUser/@id',function ($id){
    global $db;
    global $utils;
    $res = $db->delete('user',$id);
    Flight::json($utils->response(0,$res,'操作成功！'));
});

// 管理员分页查询问诊记录
Flight::route('POST /api/selectConsultation', function (){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $page=$requestData["page"];
    $limit=$requestData["limit"];
    $conditions = array(
        "userId" => $requestData["userId"],
        "memo" => $requestData["memos"]
    );
    $res = $db->getAllByPage('consultation', $page, $limit, $conditions,"memo");
    Flight::json($utils->response(0,$res,'操作成功！'));
});

// 根据问诊id删除问诊记录
Flight::route('POST /api/deleteConsultation/@id',function ($id){
    global $db;
    global $utils;
    $res = $db->delete('consultation',$id);
    Flight::json($utils->response(0,$res,'操作成功！'));
});


Flight::start();