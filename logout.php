<?php

require_once __DIR__.'/includes/ProcessData.php';

session_name(ProcessData::get_env_value("SESSION_NAME"));
session_start();
$_SESSION['name'] = '';
$_SESSION['pic'] = '';
$_SESSION['email'] = '';

header("Location: index.php");
exit;