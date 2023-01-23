<?php
require_once '../class/user.php';
require_once 'config.php';
$email = filter_input(INPUT_GET, 'email', FILTER_SANITIZE_EMAIL);
if (!empty($email) && ($_SESSION['user']['email'] == $email || empty($_SESSION['user']['email']))) {
    $user->notify($email);
} else {
    header('Location: login.php');
}
?>
