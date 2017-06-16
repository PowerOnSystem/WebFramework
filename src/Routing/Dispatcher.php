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

use PowerOn\Controller\BasicController;
use PowerOn\Network\Request;
use PowerOn\Utility\Inflector;
use PowerOn\Exceptions\DevException;
use PowerOn\Exceptions\ProdException;

/**
 * Dispatcher
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class Dispatcher {
    /**
     * Maneja las rutas
     * @var \AltoRouter
     */
    private $_router;
    /**
     * Solicitud
     * @var Request
     */
    private $_request;
    /**
     * Nombre del controlador cargado
     * @var string
     */
    public $controller;
    /**
     * Nombre de la acción ejecutada
     * @var string
     */
    public $action;
    /**
     * Instancia del controlador
     * @var BasicController
     */
    public $instance;

    public function __construct(\AltoRouter $router, Request $request) {
        $this->_router = $router;
        $this->_request = $request;
        
        $routes_file = PO_PATH_APP . DS . 'config' . DS . 'routes.php';
        if ( is_file($routes_file) ) {
            $routes = include $routes_file;
            if ( !is_array($routes) ) {
                throw new DevException(sprintf('El archivo de rutas en (%s) debe retornar un array', $routes_file), ['routes' => $routes]);
            }
            $this->_router->addRoutes($routes);
        }
    }
    
    /**
     * Busca coincidencias con la url solicitada
     * @return integer
     */
    public function handle() {
        $match = $this->_router->match($this->_request->request_path);
        if ( $match ) {
            $target = explode('#', $match['target']);
            $this->controller = $target[0];
            $this->action = key_exists(1, $target) ? $target[1] : 'index';
        } else {
            $this->controller = $this->_request->controller;
            $this->action = $this->_request->action;
        }
        
        $this->loadController();
        
        if ( !$this->instance || !method_exists($this->instance, $this->action) ) {
            throw new ProdException(404);
        }
        
        return TRUE;
    }
    
    public function force($request_controller, $request_action = 'index') {
        $this->controller = $request_controller;
        $this->action = $request_action;
        
        $this->loadController();
        if (!$this->instance) {
            throw new DevException(sprintf('No se existe la clase del controlador (%s)', $this->controller));
        }
        
        if ( !method_exists($this->instance, $this->action) ) {
            $reflection = new \ReflectionClass($this->instance);

            throw new DevException(sprintf('No existe el m&eacute;todo (%s) del controlador (%s)', 
                    $this->action, $reflection->getName()), ['controller' => $this->controller]);
        }
        
        return TRUE;
    }

    /**
     * Verifica la existencia del controlador solicitado y lo devuelve
     * @return BasicController Devuelve una instancia del controlador solicitado, devuelve FALSE si no existe
     */
    private function loadController() {
        $controller_name = Inflector::classify($this->controller) . 'Controller';
        $controller_class = 'App\\Controller\\' . $controller_name;
        
        if ( !class_exists($controller_class) ) {
            return FALSE;
        }
        
        $this->instance = new $controller_class();
        
        return TRUE;
    }
}