<?php
require_once '../class/user.php';
require_once 'config.php';
$action = filter_input(INPUT_GET,'doaction',FILTER_SANITIZE_STRING);
if ($_SESSION['user']['id'] != '') {
    if($action == 'paynow' && $_GET['payid']>0){
      $id = filter_input(INPUT_GET, 'payid', FILTER_VALIDATE_INT);
      $user->paynow($id);
      header('Location: user.php');
      exit;
    }else if($action == 'reject'){
      $id = filter_input(INPUT_GET, 'payid', FILTER_VALIDATE_INT);
      $user->rejectPayment('',$id);
      header('Location: user.php');
      exit;            
    }
    $user->userPage();
} else {
    if($action == 'reject'){
      $id = filter_input(INPUT_GET, 'payid', FILTER_VALIDATE_INT);
      $user->rejectPayment('',$id);
      header('Location: notify.php');
      exit;      
    }else if($action == 'paynow'){
      header('Location: index.php');
      exit;            
    }
    header('Location: index.php');
}
?>
