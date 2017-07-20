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

use PowerOn\Utility\Inflector;
use PowerOn\Exceptions\LogicException;
use PowerOn\Exceptions\NotFoundException;

/**
 * Dispatcher
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class Dispatcher {
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
     * Obtiene el controlador a utilizar
     * @return \PowerOn\Controller\Controller Devuelve el controlador
     */
    public function handle( \AltoRouter $router, \PowerOn\Network\Request $request ) {
        $match = $router->match($request->path);
        if ( $match ) {
            $target = explode('#', $match['target']);
            $this->controller = $target[0];
            $this->action = key_exists(1, $target) ? $target[1] : 'index';
        } else {
            $url = $request->urlToArray();
            $controller = array_shift($url);
            $action = array_shift($url);
            $this->controller = $controller ? $controller : 'index';
            $this->action = $action ? $action : 'index';
        }
        
        $handler = $this->loadController();
        
        if ( !$handler || !method_exists($handler, $this->action) ) {
            throw new NotFoundException('El sitio al que intenta ingresar no existe.');
        }
        
        return $handler;
    }
    
    /**
     * Forza la carga de un controlador
     * @param string $request_controller
     * @param string $request_action
     * @return \PowerOn\Controller\Controller
     * @throws LogicException
     */
    public function force($request_controller, $request_action = 'index') {
        $this->controller = $request_controller;
        $this->action = $request_action;
        
        $handler = $this->loadController();
        
        if ( !$handler ) {
            throw new LogicException(sprintf('No se existe la clase del controlador (%s)', $this->controller));
        }
        
        if ( !method_exists($handler, $this->action) ) {
            $reflection = new \ReflectionClass($handler);

            throw new LogicException(sprintf('No existe el m&eacute;todo (%s) del controlador (%s)', 
                    $this->action, $reflection->getName()), ['controller' => $handler]);
        }
        
        return $handler;
    }

    /**
     * Verifica la existencia del controlador solicitado y lo devuelve
     * @return \PowerOn\Controller\Controller Devuelve una instancia del controlador solicitado, devuelve FALSE si no existe
     */
    private function loadController() {
        $controller_name = Inflector::classify($this->controller) . 'Controller';
        $controller_class = $this->controller === 'system' ? 'PowerOn\\Controller\\CoreController' : 'App\\Controller\\' . $controller_name;

        if ( !class_exists($controller_class) ) {
            return FALSE;
        }
        
        return new $controller_class();
    }
}