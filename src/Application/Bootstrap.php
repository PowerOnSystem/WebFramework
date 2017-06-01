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

use PowerOn\Routing\Dispatcher;
use PowerOn\Exceptions\DevException;
use PowerOn\Exceptions\ProdException;

use Pimple\Container;

define('POWERON_ROOT', dirname(dirname(__FILE__)));



//Pimple Container
$container = new Container();

try {
    try {
        include POWERON_ROOT . DS . 'Application' . DS . 'Configuration.php';

        /* @var $dispatcher \PowerOn\Routing\Dispatcher */
        $dispatcher = $container['Dispatcher'];
        
        switch ( $dispatcher->handle() ) {
            case Dispatcher::NOT_FOUND  : throw new ProdException('Sector no encontrado', 404); 
            case Dispatcher::FOUND      :
                $dispatcher->run(); break;
            default                     : 
                throw new DevException('El dispatcher no retorn&oacute; el valor esperado.', 
                        ['dispatcher_result' => $dispatcher->result]);
        }
    } catch (DevException $d) {
        if ( PO_DEVELOPER_MODE ) {
            echo '<h1>' . $d->getMessage() . '</h1>';
            echo '<h4>' . $d->getFile() . ' ('. $d->getLine() . ')</h4>';
            echo '<h5>DEBUG:</h5>';
            var_dump($d->getContext());
            echo '<h5>TRACE:</h5>';
            var_dump(array_map(function($d) {
                return (key_exists('class', $d) ? $d['class'] : '') . 
                        (key_exists('type', $d) ? $d['type'] : '') .
                        $d['function'] . (key_exists('args', $d) ? '(' . json_encode($d['args']) . ')' : '') . ' [' . 
                        (key_exists('file', $d) ? $d['file'] : '') . '-' . 
                        (key_exists('line', $d) ? $d['line'] : '') . ']';
            }, $d->getTrace()));
        } else {
            /*  @var $logger \Monolog\Logger */
            $logger = $container['Logger'];
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
    $controller = $dispatcher->loadController('index');
    $controller->registerServices($view, $request, $router, $logger);
}
