<?php
require_once (BASE_PATH."/src/application/sessionFunctions.php");

logout();

$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https://'?'https://':'http://';
$host = $_SERVER['HTTP_HOST'];
$uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$location = $protocol.$host.$uri.'/';

header("Location: $location");
exit;
?>