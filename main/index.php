<?php
require_once '../class/user.php';
require_once '../class/request.php';
require_once 'config.php';


if($user->isLoggedIn()){
	header('Location: user.php');
	exit;
}
$user->indexHead();
$user->indexTop();
$user->loginForm();
$user->indexMiddle();
$user->registerForm();
$user->indexFooter();
?>
