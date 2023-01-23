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
    <h2>Welcome <?php print $vars['name']; ?></h2>
    <div class="pannel" > <?php showNotificationTo($vars['notifications']) ?> </div>
    <?= showLogout(); ?>
</div>

</body>
</html>
