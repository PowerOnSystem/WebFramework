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

defined('DEV_ENVIRONMENT') ?: define('DEV_ENVIRONMENT', FALSE);

//PATHS POR DEFECTO
defined('PO_PATH_ROOT') ?: define('PO_PATH_ROOT', '');
defined('PO_PATH_APP') ?: define('PO_PATH_APP', ROOT . DS . (DEV_ENVIRONMENT ? 'test' : 'app'));
defined('PO_PATH_LANGS') ?: define('PO_PATH_LANGS', PO_PATH_APP . DS . 'langs');

defined('PO_PATH_CONFIG') ?: define('PO_PATH_CONFIG', PO_PATH_APP . DS . 'config');
defined('PO_PATH_MODULES') ?: define('PO_PATH_MODULES', PO_PATH_APP . DS . 'modules');
defined('PO_PATH_WEBROOT') ?: define('PO_PATH_WEBROOT', PO_PATH_APP . DS . 'webroot');

defined('PO_PATH_VIEW') ?: define('PO_PATH_VIEW', PO_PATH_MODULES . DS . 'View');
defined('PO_PATH_TEMPLATES') ?: define('PO_PATH_TEMPLATES', PO_PATH_VIEW . DS . 'Template');
defined('PO_PATH_HELPER') ?: define('PO_PATH_HELPER', PO_PATH_VIEW . DS . 'Helper');

//DIRECTORIOS DEL WEBROOT
defined('PO_PATH_JS') ?: define('PO_PATH_JS', (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/js');
defined('PO_PATH_CSS') ?: define('PO_PATH_CSS', (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/css');
defined('PO_PATH_IMG') ?: define('PO_PATH_IMG', (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/img');


//CONFIGURACION DEL WEBMASTER
defined('PO_WEBMASTER') ?: define('PO_WEBMASTER', ['name' => 'Lucas Sosa', 'email' => 'sosalucas87@gmail.com']);