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
use PowerOn\Utility\Config;

define('POWERON_ROOT', dirname(dirname(__FILE__)));

if ( is_file(PO_PATH_CONFIG . DS . 'application.php') ) {
    Config::initialize( PO_PATH_CONFIG . DS . 'application.php' );
}

//Pimple Container
$container = new \Pimple\Container();
include POWERON_ROOT . DS . 'Application' . DS . 'Configuration.php';

/* @var $dispatcher \PowerOn\Routing\Dispatcher */
$dispatcher = $container['Dispatcher'];

try {
    //Verificación de configuración general
    include POWERON_ROOT . DS . 'Application' . DS . 'Check.php';
    
    try {
        switch ( $dispatcher->handle() ) {
            case Dispatcher::NOT_FOUND  : throw new ProdException(404, 'Sector no encontrado'); 
            case Dispatcher::FOUND      :
                $dispatcher->controller->registerServices(
                        $container['View'], $container['Request'], $container['Router'], $container['Logger']);
                $dispatcher->run($container['View']); break;
            default                     : 
                throw new DevException('El dispatcher no retorn&oacute; el valor esperado.', 
                        ['dispatcher_result' => $dispatcher->result]);
        }
    } catch (DevException $d) {
        if ( DEV_ENVIRONMENT ) {
            echo '<h1>' . $d->getMessage() . '</h1>';
            echo '<h4>' . $d->getFile() . ' ('. $d->getLine() . ')</h4>';
            echo '<h5>DEBUG:</h5>';
            var_dump($d->getContext());
            echo '<h5>TRACE:</h5>';
            var_dump(array_map(function($f) {
                return (key_exists('class', $f) ? $f['class'] : '') . 
                        (key_exists('type', $f) ? $f['type'] : '') .
                        $f['function'] . (key_exists('args', $f) ? '(' . json_encode($f['args']) . ')' : '') . ' [' . 
                        (key_exists('file', $f) ? $f['file'] : '') . '-' . 
                        (key_exists('line', $f) ? $f['line'] : '') . ']';
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
            throw new ProdException(409, 'Ocurri&oacute; un problema y se puede continuar en este momento.', $d);
        }
    }
} catch (ProdException $p) {
    $dispatcher->loadController('index');
    $dispatcher->controller->registerServices($container['View'], $container['Request'], $container['Router'], $container['Logger']);
    $dispatcher->controller->registerException($p);
    $dispatcher->run($container['View'], 'error');
} catch (\Exception $e) {
    if (DEV_ENVIRONMENT) {
        echo '<h1>' . $e->getMessage() . '</h1>';
        echo '<h4>' . $e->getFile() . ' ('. $e->getLine() . ')</h4>';
        echo '<h5>TRACE:</h5>';
        var_dump(array_map(function($d) {
            return (key_exists('class', $d) ? $d['class'] : '') . 
                    (key_exists('type', $d) ? $d['type'] : '') .
                    $d['function'] . (key_exists('args', $d) ? '(' . json_encode($d['args']) . ')' : '') . ' [' . 
                    (key_exists('file', $d) ? $d['file'] : '') . '-' . 
                    (key_exists('line', $d) ? $d['line'] : '') . ']';
        }, $e->getTrace()));
    } else {        
        /*  @var $logger \Monolog\Logger */
        $logger = $container['Logger'];
        $logger->emergency($e->getMessage(), [
            'line' => $e->getLine(),
            'file' => $e->getFile(),
            'trace' => $e->getTrace()
        ]);
        echo '<h1>Temporalmente fuera de servicio...</h1>';
        echo 'Contactese con el webmaster para m&aacute;s detalles: ';
        echo '<a href="mailto:' . PO_WEBMASTER['email'] . '">' . PO_WEBMASTER['name'] . '</a>';
    }
}