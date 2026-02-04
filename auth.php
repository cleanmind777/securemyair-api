<?php

include('jwt.php');
$SECRET_KEY = getenv('JWT_SECRET') ?: 'e%^)urD$RS7QxcsP]p4zm42A7!i[x35YJ](gJKz9qRaMk#B&hH';
$arr=[];
$headers = getallheaders();
$token = $headers['Authorization'];
try{
    $payload = JWT::decode($token, $SECRET_KEY,['HS256']);
} catch (Exception $e) {
    $arr['error'] = $e->getMessage();
    print(json_encode($arr));
    die();
}

?>