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

namespace PowerOn\Routing;

use PowerOn\Controller\Controller;
use PowerOn\Network\Request;
use PowerOn\View\View;
use PowerOn\Utility\Container;
use PowerOn\Exceptions\DevException;

/**
 * Dispatcher
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class Dispatcher {
    /**
     * Maneja las rutas
     * @var Router
     */
    private $_router;
    /**
     * Maneja la vista final
     * @var View 
     */
    private $_view;
    /**
     * Solicitud
     * @var Request
     */
    private $_request;
    /**
     * Controlador principal cargado
     * @var Controller
     */
    public $controller;
    /**
     * Resultado del handle del dispatcher
     * @var mix
     */
    public $result;
    /**
     * Sector no encontrado
     */
    const NOT_FOUND = 404;
    /**
     * Sector econtrado
     */
    const FOUND = 1;
    
    public function __construct(Router $router, Request $request, View $view) {
        $this->_router = $router;
        $this->_request = $request;
        $this->_view = $view;
    }
    
    /**
     * Busca coincidencias con la url solicitada
     * @return integer
     */
    public function handle() {
        $this->controller = $this->_router->loadController();
        $this->result = $this->controller && method_exists($this->controller, $this->_router->action) ? self::FOUND : self::NOT_FOUND;
        
        return $this->result;
    }
    
    /**
     * Lanza la acción del controlador cargado
     * @param string $forze_action forza la ejecución de un método específico
     * @throws DevException
     */
    public function run($forze_action = NULL) {
        //Cargamos la plantilla por defecto
        $this->_view->setTemplate($this->_router->action, $this->_router->controller);

        //Si todo esta OK lanzamos la acción final
        if ( $forze_action && !method_exists($this->controller, $forze_action) ) {
            $reflection = new \ReflectionClass($this->controller);
            
            throw new DevException(sprintf('No existe el m&eacute;todo (%s) del controlador (%s)', 
                    $forze_action, $reflection->getName()), ['controller' => $this->controller]);
        }
        
        $this->controller->{ $forze_action ? $forze_action : $this->_router->action }();
        
        if ( $this->_request->is('ajax') ) {
            $this->_view->ajax();
        } else {
            //Cargamos la vista del controlador en case que no sea una peticion ajax
            $this->_view
                ->set('controller', $this->_router->controller)
                ->set('action', $this->_router->action)
                ->set('url', $this->_request->path)
                ->set('queries', $this->_request->getQueries())
                ->render()
            ;
        }
    }
    
    /**
     * Ejecuta un controlador y una acción en particular
     * @param string $controller El controlador a cargar
     * @param string $action El método a lanzar
     * @param Container $container El contenedor de dependencias
     */
    public function runController($controller, $action, Container $container) {
        $this->controller = $this->_router->loadController($controller, $action);
        $container->method($this->controller, 'initialize');
        $this->run($action);
    }
}
