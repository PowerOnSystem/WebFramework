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

use PowerOn\Exceptions\LogicException;
use PowerOn\Application\PowerOn;

//Archivo de configuración de la aplicación
$config = [];
$config_file = PO_PATH_CONFIG . DS . 'application.php';
if ( is_file($config_file) ) {
    $config = include $config_file;
    if ( !is_array($config) ) {
        throw new LogicException(sprintf('El archivo (%s) debe retornar un array', $file), ['return' => $config]);
    }
}

//Creamos el framework
$poweron = new PowerOn( $config );

//Registramos el contenedor principal
$poweron->registerContainer( include POWERON_ROOT . DS . 'Application' . DS . 'Container.php' );

//Iniciamos la aplicación seleccioando el entorno adecuado
$poweron->run( DEV_ENVIRONMENT ? PowerOn::DEVELOPMENT : PowerOn::PRODUCTION );

die;

//Creamos el container de Pimple Container
/* @var $container \Pimple\Container */
$container = include POWERON_ROOT . DS . 'Application' . DS . 'Configuration.php';

//Instanciamos la clase Request
/* @var $request \PowerOn\Network\Request */
$request = $container['Request'];

//Instanciamos la clase Dispatcher
/* @var $dispatcher \PowerOn\Routing\Dispatcher */
$dispatcher = $container['Dispatcher'];

//Instanciamos la clase View que vamos a utilizar
/* @var $view \PowerOn\View\View */
$view = $container['View'];

try {
    if ( !is_file(PO_PATH_APP . DS . 'check.lock.php') ) {
        //Verificación de configuración general
        include POWERON_ROOT . DS . 'Application' . DS . 'Check.php';
    }
    
    try {

        //CSRF Protección
        /* @var $csrf \PowerOn\Form\CSRFProtection */
        if ( $request->is('post') ) {
            $csrf = $container['CSRFProtection'];
            if ( !$csrf->check($request->data('poweron_token')) ) {
                throw new ProdException(101);
            }
        }
        
        try {
            $view->buildHelpers($container);
            $view->initialize();
            
            $dispatcher->handle();
        } catch (LogicException $d) {
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
                throw new ProdException(409, 'Developer environment exception.', $d);
            }
        }
    } catch (ProdException $p) {
        $dispatcher->force('index', 'error');
        $dispatcher->instance->registerException($p);
    }
    
    
    //Verificamos que tengamos un controlador cargado
    if ( !$dispatcher->instance ) {
        throw new \Exception('No se carg&oacute; ning&uacute;n controlador');
    }

    //Cargamos la plantilla por defecto
    $view->setTemplate($dispatcher->action, $dispatcher->controller);

    //Cargamos los servicios al controlador
    $dispatcher->instance->registerServices($container);

    //Si todo esta OK lanzamos la acción final
    $dispatcher->instance->{ $dispatcher->action }();

    if ( $request->is('ajax') ) {
        $view->ajax();
    } else {
        //Cargamos la vista del controlador en case que no sea una peticion ajax
        $view
            ->set('controller', $dispatcher->controller)
            ->set('action', $dispatcher->action)
            ->set('url', $request->path)
            ->set('queries', $request->getQueries())
            ->render()
        ;
    }

    
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
        $shutdown_file = PO_PATH_VIEW . DS . 'layout' . DS . 'shutdown.phtml';
        if ( is_file($shutdown_file) ) {
            require $shutdown_file;
        } else {
            echo 'Temporalmente fuera de servicio.';
        }
    }
}