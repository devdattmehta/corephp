<!DOCTYPE html>
<html lang="en">
<head>
    <title>Bootstrap table</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="js/main.js" type="text/javascript"></script>
</head>
<body>

<div class="container">
    <h2>Welcome <?php print $_SESSION['user']['fname'] . ' ' . $_SESSION['user']['lname']; ?></h2>
    <?php 
        if(isset($_GET['splitPtm']) && $_GET['splitPtm'] == 1){
    ?>
            <div class="form-group"><a href="user.php?createNewSplit=1">Create Split Payment Account</a></div>
    <?php
        }else if(isset($_GET['createNewSplit']) && $_GET['createNewSplit'] == 1){
            include('splitPtmForm.php');
        }else{
    ?>
          <div class="form-group"><a href="user.php?splitPtm=1">Split Payment</a></div> 

          <div class="row" id="shareAmtemailtonotifiy">
            <div class="panel-heading">
              <div class="row">
                <div class="col-xs-6">
                    <h4>Payment Requested By</h4>
                </div>
              </div>
              <hr>
            </div>
            <div class="panel-body">
            <?php showNotificationTo($vars['notifications']['from']); ?>
            </div>
            <div class="panel-heading">
              <div class="row">
                <div class="col-xs-6">
                    <h4>Payment Requested To</h4>
                </div>
              </div>
              <hr>
            </div>
            <div class="panel-body">            
            <?php showNotificationFrom($vars['notifications']['to']); ?>
            </div>
          </div>

    <?php

        }
        showLogout();
    ?>
</div>

</body>
</html>
