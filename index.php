<?php

session_start();

if (!empty($_SERVER["DOCUMENT_ROOT"])) $DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"].'/';

define('__DOCUMENT_ROOT', $DOCUMENT_ROOT);

if (is_dir('Z:/home/Core/www/'))    define('__CORE_DOCUMENT_ROOT', 'Z:/home/Core/www/');
else                                define('__CORE_DOCUMENT_ROOT', $DOCUMENT_ROOT);

require_once(__CORE_DOCUMENT_ROOT."Core/Core.php");

$Core= new Core;

$Core->handler();

?>
