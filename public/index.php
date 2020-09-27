<?php
namespace web;

use Library\Http\Request;
use Application\App;

require_once("../vendor/autoload.php");

if (false !== strpos($_SERVER["REQUEST_URI"], basename(__FILE__))) {
    header("Location:" . str_replace(basename(__FILE__), "", $_SERVER["REQUEST_URI"]));
    return;
}

$app = new App(new Request());
$app->run();
