<?php
/**
 * User: denis
 * Date: 2014-05-10
 */

$session_title = include_once(__DIR__.'/config/config.session.php');
session_name($session_title);
session_start();
if(empty($_SESSION['email'])){
    header('Location: logout.php');
    exit;
}

// TODO - Complete settings page
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <!--  TODO - <link rel="shortcut icon" href="../../assets/ico/favicon.ico">-->
    <title>Settings | Money Tracker</title>
    <!-- Bootstrap core CSS -->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet" type="text/css"/>
    <link href="css/custom_bootstrap.css" rel="stylesheet" type="text/css"/>

    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="Lib/js/loading.js"></script>
    <script type="text/javascript" src="Lib/js/misc.js"></script>
    <script type="text/javascript" src="js/settings.js"></script>

    <link href="Lib/css/loading.css" rel="stylesheet" type="text/css" />
</head>
<body>

<!-- Top Nav Bar -->
<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <!-- TODO - create logo -->
            <a class="navbar-brand" href="main.php">Money Tracker</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li><a href="#" data-toggle="dropdown" id="user_menu"><img src="<?php echo $_SESSION['pic']; ?>" alt="<?php echo $_SESSION['email']; ?>" /></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="user_menu">
                        <li role="presentation" class="dropdown-header"><?php echo $_SESSION['name']; ?></li>
                        <li role="presentation" class="divider"></li>
                        <li><a href="logout.php"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- END - Top Nav Bar -->

<!-- Main body -->
<div class="container-fluid">
    <h1>Account Setting</h1>
    <div class="row">
        <div class="col-md-12 main">
            <div class="table-responsive">
                <table id="account_settings" class="table table-striped table-hover table-condensed"></table>
            </div>
        </div>
    </div>
</div>
<!-- END - Main body -->

</body>
</html>