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
    $nickname = $requestData['nickname'];
    $userdata = $db->getBy('user',"username",$username);
    if($userdata){
        $res = $utils->response(500,null,'用户已存在');
    }else{
        $newUser = array(
            'username'=>$username,
            'password'=>$password,
            'roleId'=>$roleId,
            'nickname'=>$nickname
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
    $res = $db->getAllBy('user','roleId',2);
    Flight::json($utils->response(0,$res,'查询成功'));
});

// 获取科室列表
Flight::route('GET /api/departs',function (){
    global $db;
    global $utils;
    $res = $db->getAll('depart');
    Flight::json($utils->response(0,$res,'查询成功'));
});

// 提交问诊记录
Flight::route('POST /api/inquiry',function (){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $name = $requestData['name'];
    $userId = $requestData['userId'];
    $depart = $requestData['depart'];
    $doctorId = $requestData['doctorId'];
    $sex = $requestData['sex'];
    $phone = $requestData['phone'];
    $memo = $requestData['memo'];
    $createTime = date('Y-m-d H:i:s');
    $newConsultation = array(
        'name'=>$name,
        'userId'=>$userId,
        'depart'=>$depart,
        'doctorId' => $doctorId,
        'memo' => $memo,
        'status' => 0,
        'advice' => '',
        'adviceTime' => '0000-00-00 00:00:00',
        'createTime' => $createTime,
        'sex' => $sex,
        'phone' => $phone,
        'appointmentTime' => $requestData['appointmentTime'],
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
    );
    $res = $db->getAllByPage('user', $page, $limit, $conditions,"username");
    Flight::json($utils->response(0,$res,'操作成功！'));
});

// 根据用户id修改用户
Flight::route('POST /api/updateUser/@id', function ($id){
    global $db;
    global $utils;
    $res = null;
    $requestData = Flight::request()->data;
    $nickname = $requestData["nickname"];
    $putData = array(
        "nickname"=>$nickname,
    );
    $res = $db->update('user',$id,$putData);
    Flight::json($utils->response(0,$res,'修改成功'));
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