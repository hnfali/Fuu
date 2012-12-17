<?php
use Fuu\Mvc\Application;

chdir(dirname(__DIR__));
include_once '/library/Fuu/Mvc/Application.php';

$app = new Application(include '/apps/HelloWorld/config/Application.php');
echo $app->dispatch();