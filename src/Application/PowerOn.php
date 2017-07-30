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
use PowerOn\Exceptions\PowerOnException;
use PowerOn\Exceptions\InternalErrorException;
use PowerOn\Utility\Config;

/**
 * PowerOn Clase base del framework
 * @author Lucas Sosa
 * @version 0.1.1
 */
class PowerOn {
    /**
     * Contenedor principal
     * @var \Pimple\Container
     */
    private $_container;
    /**
     * Entorno utilizado
     * @var string
     */
    private $_environment;

    /**
     * Entorno de producción
     */
    const PRODUCTION = 'prod';
    /**
     * Entorno de desarrollo
     */
    const DEVELOPMENT = 'dev';
    
    /**
     * Parámetros de configuracion del framework
     * @param array $config
     */
    public function __construct( array $config = [] ) {
        Config::initialize( $config );
    }
    
    /**
     * Contenedor de Pimple
     * @param \Pimple\Container $container El contenedor a utilizar
     */
    public function registerContainer( \Pimple\Container $container) {
        $this->_container = $container;
    }
    
    /**
     * Inicializa la aplicación
     * @param type $environment Entorno a trabajar
     */
    public function run( $environment ) {
        $application = NULL;
        if ( class_exists('\App\Application') ) {
            $application = new \App\Application( $this->_container );
            $application->initialize();
        }
        
        $this->_environment = $environment === self::DEVELOPMENT ? self::DEVELOPMENT : self::PRODUCTION;
        
        /* @var $dispatcher \PowerOn\Routing\Dispatcher */
        $dispatcher = $this->_container['Dispatcher'];
        
        /* @var $request \PowerOn\Network\Request */
        $request = $this->_container['Request'];
        
        /* @var $response \PowerOn\Network\Response */
        $response = $this->_container['Response'];
        
        try {
            try {
                //Obtenemos el View del container
                /* @var $view \PowerOn\View\View */
                $view = $this->_container['View'];
                
                //Lanzamos el metodo beforeDispatch de la clase application solo si existe
                !$application ?: $application->beforeDispatch();
                
                //El dispatcher entrega el controlador en caso de que lo encuentre utilizando AltoRouter
                $controller = $dispatcher->handle( $this->_container['AltoRouter'], $request );
                
                //Lanzamos el metodo afterDispatch de la clase application solo si existe
                !$application ?: $application->afterDispatch();
                
                //Obtenemos el layout de la aplicación desde la configuración
                $layout = Config::get('View.layout');
                
                //Establecemos el layout obtenido, si no fue especificado se utilizará el default.phtml
                $view->setLayout($layout ? $layout : 'default');
                
                //Establecemos el template a utilizar por el controlador seleccionado
                $view->setTemplate($dispatcher->action, $dispatcher->controller);
                
                //Inicializamos el View cargando los helpers predefinidos y demás operaciones
                $view->initialize();
                
                //Registramos el container en el controlador cargado
                $controller->registerContainer($this->_container);
                
                //Lanzamos la acción capturada por el dispatcher y altorouter
                $controller->{ $dispatcher->action }();
                
                //Lanzamos el metodo beforeRender de la clase application solo si existe
                !$application ?: $application->beforeRender();
                
                //Renderizamos la respuesta luego de realizar el negocio
                $response->render( $view->getRenderedTemplate() );
                
                //Lanzamos el metodo afterRender de la clase application solo si existe
                !$application ?: $application->afterRender();
            } catch (PowerOnException $e) {
                
                $e->log( $this->_container['Logger'] );
                
                if ( $this->_environment == self::DEVELOPMENT ) {
                    $this->handleDevError($e);
                } else {
                    $message = $e instanceof LogicException ? 'Error interno' : $e->getMessage();
                    throw new InternalErrorException($message, $e->getCode() ? $e->getCode() : NULL, $e);
                }
                
            } catch (\Exception $e) {
                
                if ( $this->_environment == self::DEVELOPMENT ) {
                    $this->handleExternalError($e);
                } else {
                    $reflection = new \ReflectionClass($e);
                    $logger = $this->_container['Logger'];
                    $logger->error($e->getMessage(), [
                        'type' => $reflection->getShortName(),
                        'code' => $e->getCode(),
                        'line' => $e->getLine(),
                        'file' => $e->getFile(),
                        'trace' => $e->getTrace()
                    ]);
                    throw new InternalErrorException('Error interno', $e->getCode() ? $e->getCode() : NULL, $e);
                }
                
            }
        } catch (InternalErrorException $e) {
            $this->handleProdError($e);
        }
    }
    
    /**
     * Renderiza un error en entorno desarrollo
     * @param PowerOnException $e
     */
    private function handleDevError(PowerOnException $e) {
        //Obtenemos el Response del contenedor
        /* @var $response \PowerOn\Network\Response */
        $response = $this->_container['Response'];
        
        //Obtenemos el View a trabajar del contenedor
        /* @var $view \PowerOn\View\View */
        $view = $this->_container['View'];
        
        //Guardamos la excepción a ser tratada en una variabla llamada exception
        $view->set('exception', $e);
        
        //Renderizamos la respuesta otorgada por el View
        $response->render( 
            $view->getCoreRenderedTemplate(
                //Plantilla core de error en modo desarrollo
                'Template' . DS . 'Error' . DS . 'default.phtml',
                
                //Plantilla layout general para el modo desarrollo
                'Template' . DS . 'Layout' . DS . 'layout.phtml'
            ), $e->getCode() 
        );
    }
    
    /**
     * Renderiza un error en entorno producción
     * @param PowerOnException $e
     */
    private function handleProdError(PowerOnException $e) {
        //Obtenemos el Response del contenedor
        /* @var $response \PowerOn\Network\Response */
        $response = $this->_container['Response'];
        
        //Establecemos la ruta donde se encuentran las plantillas de error
        $path_errors = PO_PATH_TEMPLATES . DS . 'error';
        
        //Verificamos si existe una plantlla predefinida para este error con el siguiente formato "error-{ codigo_error }.phtml"
        if ( is_file($path_errors . DS . 'error-' . $e->getCode() . '.phtml') || is_file($path_errors . DS . 'default.phtml') ) {
            //Si existe una plantilla para este error obtenemos el View del contenedor
            /* @var $view \PowerOn\View\View */
            $view = $this->_container['View'];
            
            //Establecemos la plantilla encontrada
            $view->setTemplate( is_file($path_errors . DS . 'error-' . $e->getCode() . '.phtml') ? 
                    'error-' . $e->getCode() : 'default.phtml', 'error');
            
            //Obtenemos el layout del error según la configuración de la aplicación
            $error_layout = Config::get('Error.layout');
            
            //Establecemos el layout configurado en caso de que exista.
            $view->setLayout($error_layout);
            
            //Guardamos en una variable la excepcion obtenida
            $view->set('exception', $e);
            
            //Renderizamos la plantilla y guardamos el resultado para mostrarlo luego
            $render = $view->getRenderedTemplate();
        } else {
            //Si no existe una plantilla predefinida para este error se utiliza el renderizado universal de la excepción
            $render = $e->getRenderedError();
        }

        //Enviamos el renderizado a la respuesta.
        $response->render( $render, $e->getCode() );
    }
    
    /**
     * Renderiza un error externo al framework
     * @param \Exception $e
     */
    private function handleExternalError(\Exception $e) {
        $reflection = new \ReflectionClass($e);
        echo '<header>';
            echo '<h1>Exception: ' . $reflection->getName() . '</h1>';
            echo '<h2>Message: ' . $e->getMessage() . '</h2>';
            echo '<h3>' . $e->getFile() . ':' . $e->getLine() . '</h3>';
        echo '</header>';
        if ( method_exists($e, 'getContext') ) {
            echo 'Debug:';
            !d( $e->getContext() );
        }
    }
}