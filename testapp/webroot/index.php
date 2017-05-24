<?php
/*
 * Archivo de testeo del framework
 */
session_start();

define('ROOT', dirname(dirname(dirname(__FILE__))));
define('DS', DIRECTORY_SEPARATOR);

require ROOT . DS . 'vendor' . DS . 'autoload.php';