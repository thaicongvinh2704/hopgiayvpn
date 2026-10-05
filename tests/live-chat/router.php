<?php
$root=__DIR__.'/runtime/wp';
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if($path && is_file($root.$path) && !str_ends_with($path,'.php'))return false;
if(str_starts_with($path,'/wp-json/')){$_GET['rest_route']=substr($path,8);require $root.'/index.php';return true;}
if($path && is_file($root.$path) && str_ends_with($path,'.php')){require $root.$path;return true;}
if($path==='/wp-admin/' || $path==='/wp-admin'){require $root.'/wp-admin/index.php';return true;}
require $root.'/index.php';
