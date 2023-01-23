<?php

$data = file_get_contents("php://input");

require_once '../class/user.php';
require_once 'config.php';

if(isset($_POST['username']) && isset($_POST['password'])){
    $email = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
}else{
    $data = json_decode($data,true);
    var_dump($data);
    //for api call;
    $email = $data['username'];
    $password = $data['password'];
}

if ($user->login($email, $password)) {
    die($user->success);
} else {
    die($user->error);
}
