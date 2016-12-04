<?php

require_once __DIR__.'/includes/initialise_env.php';

session_name(getenv("SESSION_NAME"));
session_start();
$_SESSION['name'] = '';
$_SESSION['pic'] = '';
$_SESSION['email'] = '';

header("Location: index.php");
exit;