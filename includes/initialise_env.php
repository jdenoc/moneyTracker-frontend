<?php

require_once __DIR__.'/../vendor/autoload.php';

$dot_env = new \Dotenv\Dotenv(__DIR__.DIRECTORY_SEPARATOR.'..');
$dot_env->load();