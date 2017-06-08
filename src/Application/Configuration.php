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

/* @var $container \Pimple\Container */


$container['Logger'] = function() {
    $logger = new Monolog\Logger('PowerOn');
    if ( DEV_ENVIRONMENT ) {
        $handler = new \Monolog\Handler\BrowserConsoleHandler();
        $formatter = new Monolog\Formatter\LineFormatter('%level_name% > %message%');
        $handler->setFormatter($formatter);
    } else {
        $handler = new Monolog\Handler\StreamHandler(ROOT . DS . 'error.log');
    }
    $logger->pushHandler($handler);
    
    return $logger;
};

$container['Request'] = function() {
    return new \PowerOn\Network\Request();
};

$container['Router'] = function($c) {
    return new \PowerOn\Routing\Router($c['Request']);
};

$container['Dispatcher'] = function($c) {
    return new \PowerOn\Routing\Dispatcher($c['Router'], $c['Request']);
};

$container['View'] = function() {
    $view_file = PO_PATH_VIEW . DS . 'AppView.php';
    if ( !is_file($view_file) ) {
        $view = new PowerOn\View\View();
    } else {
        include_once $view_file;
        $view = new \App\View\AppView();
    }
    
    $view->buildHelpers();
    $view->initialize();
    
    return $view;
};