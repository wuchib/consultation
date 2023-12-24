<?php
class Utils{
    public function __construct(){

    }
    public function response($status,$result,$msg){
        $data = array(
            'msg'=>$msg ,
            'code'=>$status,
            'result'=>$result
        );
        return $data;
    }
}