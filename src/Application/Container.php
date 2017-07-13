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
        $view = new PowerOn\View\View();
    } else {
        include_once $view_file;
        $view = new \App\View\AppView();
    }
    
    $view->buildHelpers($c);
    
    return $view;
};

$container['Database'] = function () {
    if ( class_exists('\PowerOn\Database\Database') ) {
        $host = Config::get('DataBaseService.host');
        $user = Config::get('DataBaseService.user');
        $password = Config::get('DataBaseService.password');
        $database = Config::get('DataBaseService.database');
        $port = Config::get('DataBaseService.port');
        
        try {
            //Creamos un servicio PDO para la base de datos MySQL con la clase que viene en la libreria
            $service = new PDO(sprintf('mysql:host=%s;dbname=%s;port=%s', $host, $database, $port), $user, $password);
            
            //Creamos el controlador de la base de datos
            $database = new PowerOn\Database\Database( $service );
            
        } catch (PDOException $e) {
            throw new \PowerOn\Exceptions\DevException($e->getMessage(), 
                    ['error_code' => $e->getCode(), 'file' => $e->getFile(), 'line' => $e->getLine()], $e);
        }

        return $database;
    }
    
    return NULL;
};

return $container;