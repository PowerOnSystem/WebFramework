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
use PowerOn\Authorizer\UserCredentials;

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
     * Logger
     * @var \Monolog\Logger
     */
    private $_logger;
    
    /**
     * Regular expression for action names
     *
     * @var string
     */
    const ACTION = 'index|show|add|create|edit|update|remove|del|delete|view|item';
    /**
     * Regular expression for years
     *
     * @var string
     */
    const YEAR = '[12][0-9]{3}';
    /**
     * Regular expression for months
     *
     * @var string
     */
    const MONTH = '0[1-9]|1[012]';
    /**
     * Regular expression for days
     *
     * @var string
     */
    const DAY = '0[1-9]|[12][0-9]|3[01]';
    /**
     * Regular expression for auto increment IDs
     *
     * @var string
     */
    const ID = '[0-9]+';
    /**
     * Regular expression for UUIDs
     *
     * @var string
     */
    const UUID = '[A-Fa-f0-9]{8}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{4}-[A-Fa-f0-9]{12}';
    
    public function __construct(Request $request, \Monolog\Logger $logger) {
        $this->_request = $request;
        $this->_logger = $logger;
        
        $routes_file = ROOT . DS . 'config' . DS . 'routes.php';
        if ( is_file($routes_file) ) {
            $routes = include ROOT . DS . 'config' . DS . 'routes.php';
            foreach ($routes as $route) {
                if ( is_array($route) ) {
                    $pattern = '/^' . str_replace('/', '\/', $route[key_exists('pattern', $route) ? 'pattern' : 0]) . '$/';
                    $this->_collections[$pattern] = key_exists(1, $route) && is_array($route[1]) ? $route[1] : NULL;
                }
            }
        }
    }
    
    /**
     * Carga el controlador solicitado
     * @param UserCredentials $credentials
     * @return Controller
     */
    public function loadController($force_controller = NULL, $force_action = NULL) {
        $this->controller = $force_controller ? $force_controller : ($this->_request->url(0) ? $this->_request->url(0) : $this->controller);
        $this->action = $force_action ? $force_action : ($this->_request->url(1) ? $this->_request->url(1) : $this->action);

        //Verificamos la ruta para obtener el controlador y la accion
        $this->match($force_controller, $force_action);
        
        //Cargamos el controlador resultante
        $controller_name = Inflector::classify($this->controller) . 'Controller';
        $controller_file = PO_PATH_APP . DS
                . 'modules' . DS . 'Controller' . DS . $controller_name . '.php';
        $controller_class = 'App\\Controller\\' . $controller_name;

        if ( !is_file($controller_file) ) {
            $this->_logger->error( sprintf('No se existe la clase del controlador (%s)', $this->controller), 
                [
                    'class' => $controller_class, 
                    'request' => $this->controller, 
                    'name' => $controller_name,
                    'file' => $controller_file
                ] );
            return FALSE;
        } else {
            include $controller_file;
        }
        
        if ( !class_exists($controller_class) ) {
            $this->_logger->error( sprintf('No se existe la clase del controlador (%s)', $this->controller), 
                [
                    'class' => $controller_class, 
                    'request' => $this->controller, 
                    'name' => $controller_name,
                    'file' => $controller_file
                ] );
            
            return FALSE;
        }
        
        return new $controller_class();
    }
    
    
    /**
     * Verificamos si existen rutas apuntadas
     */
    public function match($force_controller = NULL, $force_action = NULL) {
        $path = $force_controller ? $force_controller . '/' . $force_action : $this->_request->path;
        
        $url = $path . (substr($path, strlen($path) - 1) != '/' ? '/' : '');

        foreach ($this->_collections as $pattern => $match) {
            if ( preg_match($pattern, $url) && $match ) {
                $this->controller = $match['controller'];
                $this->action = $match['action'];
            }
        }
    }
    
    /**
     * Crea una URL nueva a partir de los datos entregados
     * @param array $url
     * @return string
     */
    public function buildUrl( array $url = [] ) {
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
        
        $result = (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '') 
            . ( key_exists('controller', $url) ? '/' . $url['controller'] : (key_exists('action', $url) ? '/' . CNC_SYSTEM_DEFAULT_CONTROLLER : ''))
            . ( key_exists('action', $url) ? '/' . $url['action'] : ($vars ? CNC_SYSTEM_DEFAULT_ACTION : '') )
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
        $path = explode('/', $this->_request->url);
        
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
        
        $last_url = (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '') . 
            '/' . ( $url_action == CNC_SYSTEM_DEFAULT_ACTION && $url_controller == CNC_SYSTEM_DEFAULT_CONTROLLER && !$url ? '' : $url_controller . '/' ) .
            ( ($url_action == CNC_SYSTEM_DEFAULT_ACTION && $url) || $url_action != CNC_SYSTEM_DEFAULT_ACTION ? $url_action . '/' : '' ) . 
            implode('/', $url);
        
        $gets = $gets_request + $this->_request->getQueries();
        foreach ($gets_remove as $gr) {
            if (key_exists($gr, $gets)) {
                unset($gets[$gr]);
            }
        } 
        array_walk($gets, function(&$v, $k, $path) {
            $v = $k . '=' . ($k == 'return' ? base64_encode($path) : $v);
        }, $this->_request->full_path);
        
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
            $query = \PowerOn\Core\PowerOnArrayTrim($push, 'query');
            array_walk($query, function(&$v, $k, $path) {
                $v = $k . '=' . ($k == 'return' ? base64_encode($path) : $v);
            }, $this->_request->full_path);
        }
        $path = substr($this->_request->path, -1) == '/' ? 
                substr($this->_request->path, 0, strlen($this->_request->path) - 1) :
                $this->_request->path;
        return (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '') . '/' . $path . '/' . implode('/', $push) .
                ( $query ? '/?' . implode('&', $query) : '' );
    }
}
