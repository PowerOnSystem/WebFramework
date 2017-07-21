<?php
/*
 * Copyright (C) PowerOn Sistemas
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

//Si no esta definido el DIRECTORY SEPARATOR
defined('DS') ?: define('DS', DIRECTORY_SEPARATOR);

/**
 * Carpeta raiz del framework
 */
define('POWERON_ROOT', dirname(dirname(__FILE__)));

//Si no esta definido el entorno
defined('DEV_ENVIRONMENT') ?: define('DEV_ENVIRONMENT', FALSE);

//Si no esta definida la carpeta raiz de la aplicación
defined('PO_PATH_APP') ?: define('PO_PATH_APP', ROOT);

/**
 * Subdirectorio donde se va a acceder a la web
 */
defined('PO_PATH_ROOT') ?: define('PO_PATH_ROOT', NULL);

/**
 * Archivos Log
 */
define('PO_PATH_LOGS', PO_PATH_APP . DS . 'logs');
/**
 * Lenguajes
 */
define('PO_PATH_LANGS', PO_PATH_APP . DS . 'langs');
/**
 * Configuración
 */
define('PO_PATH_CONFIG', PO_PATH_APP . DS . 'config');
/**
 * Contenido de la web
 */
define('PO_PATH_APP_CONTENT', PO_PATH_APP . DS . 'src');
/**
 * Webroot de la web
 */
define('PO_PATH_WEBROOT', PO_PATH_APP . DS . 'webroot');
/**
 * Vistas, Templates y Helpers a utilizar
 */
define('PO_PATH_VIEW', PO_PATH_APP_CONTENT . DS . 'View');
/**
 * Templates de la web
 */
define('PO_PATH_TEMPLATES', PO_PATH_VIEW . DS . 'Template');
/**
 * Helpers de la web
 */
define('PO_PATH_HELPER', PO_PATH_VIEW . DS . 'Helper');
/**
 * Webroot carpeta javascript
 */
define('PO_PATH_JS', (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/js');
/**
 * Webroot carpeta de archivos de estilos css
 */
define('PO_PATH_CSS', (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/css');
/**
 * Webroot carpeta de imágenes
 */
define('PO_PATH_IMG', (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/img');

error_reporting(E_ALL);

//Configuracion de reporte de errores
if ( DEV_ENVIRONMENT ) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('logs_error', '0');
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('logs_error', '1');
    ini_set('error_log', PO_PATH_LOGS . DS . 'php.log');
}

