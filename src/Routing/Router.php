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
use PowerOn\Controller\Controller;
use PowerOn\Exceptions\DevException;
use PowerOn\Network\Request;

/**
 * Router
 * @author Lucas Sosa
 * @version 0.1
 */
class Router {
    /**
     * El nombre del controlador a cargar
     * @var string 
     */
    public $controller = 'index';
    
    /**
     * El nombre de la accion a llamar
     * @var string 
     */
    public $action = 'index';

    /**
     * Las rutas del modulo actual
     * @var array
     */
    private $_collections = [];
    
    /**
     * Solicitud del cliente
     * @var Request
     */
    private $_request;
    /**
     * AltoRouter class
     * @var \AltoRouter
     */
    private $_altorouter;
    
    public function __construct(Request $request, \AltoRouter $altorouter) {
        $this->_request = $request;        
        $this->_altorouter = $altorouter;
        
        $routes_file = PO_PATH_APP . DS . 'config' . DS . 'routes.php';
        if ( is_file($routes_file) ) {
            $this->_collections = include $routes_file;
        }
    }
    
    /**
     * Carga el controlador solicitado
     * @param string $force_controller Forza la carga de un controlador especifico
     * @return Controller
     */
    public function loadController($force_controller = NULL) {
        $this->controller = $force_controller ? $force_controller : ($this->_request->url(0) ? $this->_request->url(0) : $this->controller);
        $this->action = $this->_request->url(1) ? $this->_request->url(1) : $this->action;

        //Verificamos la ruta para obtener el controlador y la accion
        if ( !$force_controller && $this->_collections ) {
            $this->match();
        }
        
        //Cargamos el controlador resultante
        $controller_name = Inflector::classify($this->controller) . 'Controller';
        $controller_file = PO_PATH_APP . DS
                . 'modules' . DS . 'Controller' . DS . $controller_name . '.php';
        $controller_class = 'App\\Controller\\' . $controller_name;

        if ( is_file($controller_file) && !class_exists($controller_class) ) {
            include $controller_file;
        }
        
        if ( !class_exists($controller_class) ) {
            if ( $force_controller ) {
                throw new DevException( 
                    sprintf('No se existe la clase del controlador (%s)', $this->controller), [
                        'class' => $controller_class, 
                        'request' => $this->controller, 
                        'name' => $controller_name,
                        'file' => $controller_file
                    ] 
                );
            }
            return FALSE;
        }
        
        return new $controller_class();
    }
    
    
    /**
     * Verificamos si existen rutas apuntadas
     */
    private function match() {
        foreach ($this->_collections as $param => $route) {
            $this->_altorouter->map('GET', $param, NULL, key_exists(2, $route) ? $route[2] : NULL);
            if ( $this->_altorouter->match() ) {
                if ( !key_exists(0, $route) || !key_exists(1, $route) ) {
                    throw new DevException(sprintf('La ruta encontrada esta mal configurada', $param), 
                            ['route' => $route, 'param' => $param]);
                }
                $this->controller = $route[0];
                $this->action = $route[1];
            }
        }
    }
    
    public function routeExist($name) {
        try {
            return $this->_altorouter->generate($name);
        } catch (\Exception $e) {
            return FALSE;
        }
    }
    
    /**
     * Crea una URL nueva a partir de los datos entregados
     * @param array $url
     * @param string $name [Opcional] Nombre de la url existente
     * @return string
     */
    public function buildUrl( array $url = [], $name = NULL ) {
        if ($name) {
            return $this->_altorouter->generate($name, $url);
        }
        
        $vars = [];
        foreach ($url as $k => $u) {
            if ($k !== 'controller' && $k !== 'action' && $k !== 'query') {
                $vars[] = $u;
            }
        }
        
        $gets = key_exists('query', $url) ? $url['query'] : [];
        array_walk($gets, function(&$v, $k, $path) {
            $v = $k . '=' . ($k == 'return' ? base64_encode($path) : $v);
        }, $this->_request->full_path);
        
        $result = (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') 
            . ( key_exists('controller', $url) ? '/' . $url['controller'] : (key_exists('action', $url) ? '/index' : ''))
            . ( key_exists('action', $url) ? '/' . $url['action'] : ($vars ? 'index' : '') )
            . ( $vars ? '/' . implode('/', $vars) : '' )
            . ( $gets ? '/?' . implode('&', $gets) : '' );
        
        return $result ? $result : '/';
    }
    
    /**
     * Modifica una URL agregando o quitando variables
     * @param array $add [Opcional] la URL a agregar
     * @param array $remove [Opcional] la URL a remover
     * @return string
     */
    public function modifyUrl(array $add = [], array $remove = [], $controller = NULL, $action = NULL) {
        $path = explode('/', $this->_request->path);
        $gets_remove = key_exists('query', $remove) ? $remove['query'] : [];
        if (key_exists('query', $remove)) {
            unset($remove['query']);
        }
        
        $url = array_filter(array_diff_key($path, array_fill_keys($remove, FALSE), $add) + $add, function ($var) {
            return ($var !== NULL && $var !== FALSE && $var !== '');
        });
        
        $gets_request = key_exists('query', $url) ? $url['query'] : [];
        
        if (key_exists('query', $url)) {
            unset($url['query']);
        }

        ksort($url);
        array_walk($url, function(&$v, $k) {
            $v = (is_string($k) ? $k . '-' : '') . $v;
        });
        
        $url_controller = $controller ? $controller : $this->_request->controller;
        $url_action = $action ? $action : $this->_request->action;
        
        $last_url = (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . 
            '/' . ( $url_action == 'index' && $url_controller == 'index' && !$url ? '' : $url_controller . '/' ) .
            ( ($url_action == 'index' && $url) || $url_action != 'index' ? $url_action . '/' : '' ) . 
            implode('/', $url);
        
        $gets = $gets_request + $this->_request->getQueries();
        foreach ($gets_remove as $gr) {
            if (key_exists($gr, $gets)) {
                unset($gets[$gr]);
            }
        } 
        array_walk($gets, function(&$v, $k) {
            $v = $k . '=' . $v;
        });
        
        return $last_url . ($gets ? (substr($last_url, -1) == '/' ? '' : '/') . '?' . implode('&', $gets) : '');
    }
    
    /**
     * Agrega un valor al final de la url
     * @param array $push la URL a agregar al final
     * @return string
     */
    public function pushUrl(array $push = []) {
        $query = [];
        if ( key_exists('query', $push) ) {
            $query = \PowerOn\Application\array_trim($push, 'query');
            array_walk($query, function(&$v, $k) {
                $v = $k . '=' . $v;
            });
        }
        $path = substr($this->_request->path, -1) == '/' ? 
                substr($this->_request->path, 0, strlen($this->_request->path) - 1) :
                $this->_request->path;
        return (PO_PATH_ROOT ? '/' . PO_PATH_ROOT : '') . '/' . $path . '/' . implode('/', $push) .
                ( $query ? '/?' . implode('&', $query) : '' );
    }
}
