<?php
/**
 * User: denis
 * Date: 2014-05-10
 */

$session_title = include_once(__DIR__.'/includes/config.session.php');
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

    <link href="css/switch.css" rel="stylesheet" type="text/css" />
    <link href="css/loading.css" rel="stylesheet" type="text/css" />
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
    <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
<!--            TODO - create proper headings -->
<!--            <ul id="account_display" class="nav nav-sidebar">-->
<!--                <li><h4>Accounts</h4></li>-->
<!--                <li class="active" onclick="resetFilter();displayAccount([],2)"><a href="#">Overview <span class="is_filtered">(filtered)</span></a></li>-->
<!--            </ul>-->
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">



        </div>
    </div>
</div>
<!-- END - Main body -->

</body>
</html>