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
namespace PowerOn\Application;

use PowerOn\Utility\Container;
use PowerOn\Routing\Dispatcher;
use PowerOn\Exceptions\DevException;
use PowerOn\Exceptions\ProdException;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Formatter\LineFormatter;

define('POWERON_ROOT', dirname(dirname(__FILE__)));

//Monolog logger config
$logger = new Logger('PowerOn');
if ( PO_DEVELOPER_MODE ) {
    $handler = new BrowserConsoleHandler();
    $formatter = new LineFormatter('%level_name% > %message%');
    $handler->setFormatter($formatter);
} else {
    $handler = new StreamHandler(ROOT . DS . 'error.log');
}
$logger->pushHandler($handler);

//Container build
$container = new Container();
try {
    try {
        $container->buildDependencies(POWERON_ROOT . DS . 'Application' . DS . 'Dependencies.php');
        $container->pushDependency('Monolog\Logger', $logger);

        /* @var $dispatcher \PowerOn\Routing\Dispatcher */
        $dispatcher = $container->get('PowerOn\Routing\Dispatcher');

        switch ( $dispatcher->handle() ) {
            case Dispatcher::NOT_FOUND  : throw new ProdException('Sector no encontrado', 404);
            case Dispatcher::FOUND      : $dispatcher->run(); break;
            default                     : 
                throw new DevException('El dispatcher no retorn&oacute; el valor esperado.', ['dispatcher_result' => $dispatcher->result]);
        }
    } catch (DevException $d) {
        if ( PO_DEVELOPER_MODE ) {
            echo '<h1>' . $e->getMessage() . '</h1>';
            echo '<h4>' . $e->getFile() . ' ('. $e->getLine() . ')</h4>';
            echo '<h5>DEBUG:</h5>';
            var_dump($d->getContext());
            echo '<h5>TRACE:</h5>';
            var_dump(array_map(function($e) {
                return (key_exists('class', $e) ? $e['class'] : '') . 
                        (key_exists('type', $e) ? $e['type'] : '') .
                        $e['function'] . (key_exists('args', $e) ? '(' . json_encode($e['args']) . ')' : '') . ' [' . 
                        (key_exists('file', $e) ? $e['file'] : '') . '-' . 
                        (key_exists('line', $e) ? $e['line'] : '') . ']';
            }, $e->getTrace()));
        } else {
            $logger->error($d->getMessage(), [
                'line' => $d->getLine(),
                'file' => $d->getFile(),
                'trace' => $d->getTrace(),
                'context' => $d->getContext()
            ]);
            throw new ProdException('Ocurri&oacute; un problema y se puede continuar en este momento.', 409, $d);
        }
    }
} catch (ProdException $p) {
    /* @var $view \PowerOn\View\View */
    $view = $container->get('PowerOn\View\View');
    $view->error(['title' => 'Error', 'message' => $p->getMessage(), 'code' => $p->getCode()]);
    
    $dispatcher->runController('index', 'error', $container);
}
