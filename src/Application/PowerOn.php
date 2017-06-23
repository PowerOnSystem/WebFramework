<?php

/*
 * Copyright (C) Makuc Julian & Makuc Diego S.H.
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
        $this->_environment = $environment === self::DEVELOPMENT ? self::DEVELOPMENT : self::PRODUCTION;
        
        /* @var $dispatcher \PowerOn\Routing\Dispatcher */
        $dispatcher = $this->_container['Dispatcher'];
        
        /* @var $request \PowerOn\Network\Request */
        $request = $this->_container['Request'];
        
        /* @var $response \PowerOn\Network\Response */
        $response = $this->_container['Response'];
        
        try {
            try {
                /* @var $view \PowerOn\View\View */
                $view = $this->_container['View'];
                $view->initialize();
                
                $controller = $dispatcher->handle( $this->_container['AltoRouter'], $request );

                $view->setLayout(Config::get('View.layout'));
                $view->setTemplate($dispatcher->action, $dispatcher->controller);

                $controller->registerContainer($this->_container);
                $controller->{ $dispatcher->action };

                $response->render( $view->getRenderedTemplate() );

            } catch (PowerOnException $e) {
                if ( $this->_environment == self::DEVELOPMENT ) {
                    $this->handleDevError($e);
                } else {
                    throw new InternalErrorException($e->getMessage(), $e->getCode(), $e);
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
        $e->log( $this->_container['Logger'] );
        
        /* @var $response \PowerOn\Network\Response */
        $response = $this->_container['Response'];
        
        /* @var $view \PowerOn\View\View */
        $view = $this->_container['View'];

        $view->set('exception', $e);
        
        $response->render( 
            $view->getCoreRenderedTemplate(
                'Exceptions' . DS . 'Template' . DS . 'error.phtml',
                'Exceptions' . DS . 'Template' . DS . 'layout.phtml'
            ), $e->getCode() 
        );
    }
    
    /**
     * Renderiza un error en entorno producción
     * @param PowerOnException $e
     */
    private function handleProdError(PowerOnException $e) {
        $e->log( $this->_container['Logger'] );
        /* @var $response \PowerOn\Network\Response */
        $response = $this->_container['Response'];
        if ( is_file(PO_PATH_TEMPLATES . DS . 'error' . DS . 'error-' . $e->getCode() . '.phtml') ) {
            /* @var $view \PowerOn\View\View */
            $view = $this->_container['View'];
            $view->setTemplate('error-' . $e->getCode(), 'error');
            $error_layout = Config::get('Error.layout');
            $view->setLayout($error_layout ? $error_layout : 'error');
            $view->set('exception', $e);
            $render = $view->getRenderedTemplate();
        } else {
            $render = $e->getRenderedError();
        }

        $response->render( $render, $e->getCode() );
    }
}
