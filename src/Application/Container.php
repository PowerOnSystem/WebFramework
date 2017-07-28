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

$container = new \Pimple\Container();

$container['Logger'] = function() {
    $logger = new Monolog\Logger('PowerOn');
    if ( DEV_ENVIRONMENT ) {
        $handler = new \Monolog\Handler\BrowserConsoleHandler();
        $formatter = new Monolog\Formatter\LineFormatter('%level_name% > %message%');
        $handler->setFormatter($formatter);
    } else {
        $handler = new Monolog\Handler\StreamHandler(PO_PATH_LOGS . DS . 'error.log');
    }
    $logger->pushHandler($handler);
    
    return $logger;
};

$container['Request'] = function() {
    return new \PowerOn\Network\Request();
};

$container['Response'] = function() {
    return new \PowerOn\Network\Response();
};

$container['AltoRouter'] = function() {
    $router = new AltoRouter();
    $routes_file = PO_PATH_APP . DS . 'config' . DS . 'routes.php';
    if ( is_file($routes_file) ) {
        $router->addRoutes( include $routes_file );
    }
    
    $router->map('GET', 'error/[i:error]', 'system#error', 'poweron_error');
    
    return $router;
};

$container['Dispatcher'] = function($c) {
    return new \PowerOn\Routing\Dispatcher($c['AltoRouter'], $c['Request']);
};

$container['CSRFProtection'] = function($c) {
    return new \PowerOn\Form\CSRFProtection($c['Request']);
};

$container['View'] = function($c) {
    $view_file = PO_PATH_VIEW . DS . 'AppView.php';
    if ( !is_file($view_file) ) {
        $view = new PowerOn\View\View($c);
    } else {
        include_once $view_file;
        $view = new \App\View\AppView($c);
    }
    return $view;
};

$container['Database'] = function () {
    if ( class_exists('\PowerOn\Database\Database') ) {
        $host = \PowerOn\Utility\Config::get('DataBaseService.host');
        $user = \PowerOn\Utility\Config::get('DataBaseService.user');
        $password = \PowerOn\Utility\Config::get('DataBaseService.password');
        $database = \PowerOn\Utility\Config::get('DataBaseService.database');
        $port = \PowerOn\Utility\Config::get('DataBaseService.port');
        
        if ( !$host || !$database ) {
            throw new \PowerOn\Exceptions\LogicException('Debe configurar la base de datos antes de utilizarla, '
                    . 'vaya al archio de configuraci&oacute;n (generalmente ubicado en config/application.php) '
                    . 'y agregue el valor "DataBaseService" al array de configuraci&oacute;n con sus respectivos par&aacute;metros.');
        }
        
        try {
            //Creamos un servicio PDO para la base de datos MySQL con la clase que viene en la libreria
            $service = new PDO(sprintf('mysql:host=%s;dbname=%s;port=%s', $host, $database, $port), $user, $password);
            
            //Creamos el controlador de la base de datos
            $database = new PowerOn\Database\Database( $service );
            
        } catch (PDOException $e) {
            throw new \PowerOn\Exceptions\LogicException($e->getMessage(), 
                    ['error_code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()], $e);
        }

        return $database;
    }
    
    return NULL;
};

$container['Authorization'] = function($c) {
    if ( class_exists('\PowerOn\Authorization\Authorization') ) {
        $db = $c['Database'];
        $config = [ 
            'db_pdo' => $db->service(),
            'login_email_mode' => PowerOn\Utility\Config::get('AuthorizationService.login_email_mode'),
            'login_error_max_chances' => PowerOn\Utility\Config::get('AuthorizationService.login_error_max_chances'),
            'login_error_wait_time' => PowerOn\Utility\Config::get('AuthorizationService.login_error_wait_time'),
            'login_check_ban' => PowerOn\Utility\Config::get('AuthorizationService.login_check_ban'),
            'login_session_time' => PowerOn\Utility\Config::get('AuthorizationService.login_session_time'),
            'password_min_length' => PowerOn\Utility\Config::get('AuthorizationService.password_min_length'),
            'password_max_length' => PowerOn\Utility\Config::get('AuthorizationService.password_max_length'),
            'username_min_length' => PowerOn\Utility\Config::get('AuthorizationService.username_min_length'),
            'username_max_length' => PowerOn\Utility\Config::get('AuthorizationService.username_max_length'),
            'access_level_min_val' => PowerOn\Utility\Config::get('AuthorizationService.access_level_min_val'),
            'access_level_max_val' => PowerOn\Utility\Config::get('AuthorizationService.access_level_max_val'),
        ];
        
        
        return new \PowerOn\Authorization\Authorization( array_filter($config) );
    }
    
    return NULL;
};

return $container;