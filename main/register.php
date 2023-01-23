<?php
        
require_once '../class/user.php';
require_once 'config.php';

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
$lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
$pass = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
$image = array();
if(isset($_FILES['image']))
    $image = $_FILES['image'];

if ($user->registration($email, $fname, $lname, $pass, $image)) {
    die(json_encode(array("message" => $user->msg, "status" => true)));
} else {
    //$user->printMsg();
    die($user->error);
}
