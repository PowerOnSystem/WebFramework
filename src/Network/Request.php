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

namespace PowerOn\Network;
use Detection\MobileDetect;

/**
 * Maneja la solicitud del cliente
 *
 * @author Lucas Sosa
 * @version 0.1
 */
class Request {
    /**
     * Parámetros GET
     * @var array 
     */
    private $_get = [];
    /**
     * URL Solicitada
     * @var array
     */
    private $_url = [];
    /**
     * Metodo utilizado en la solicitud
     * @var string 
     */
    private $_method;
    /**
     * Detector de dispositivos
     * @var MobileDetect
     */
    private $_device;
    /**
     * Controlador de sesiones
     * @var Session
     */
    private $_session;
    /**
     * La url completa finaliza SIN BARRA "/"
     * @var string 
     */
    public $path;
    /**
     * La url completa con las query
     * @var string
     */
    public $full_path;

    

    public function __construct() {
        $this->full_path = $this->server('REQUEST_URI');
        $path = strpos($this->full_path, '?') ? substr($this->full_path, 0, strpos($this->full_path, '?')) : $this->full_path;
                
        $query_strings = filter_input_array(INPUT_GET, FILTER_SANITIZE_ENCODED);
        
        $this->_get = $query_strings ? array_map(function($value) {
            return htmlentities(urldecode($value));
        }, $query_strings) : [];
        
        
        $url = array_filter(array_map(function($value) {
            return htmlentities(urldecode($value));
        }, explode('/', $path)));
        
        if ( in_array(PO_PATH_ROOT, $url, TRUE) ) {
            array_shift($url);
        }

        $this->_url = array_values($url);
        $this->path = implode('/', $url);

        $type = $this->server('REQUEST_METHOD');
        $ajax = strtolower(filter_input(INPUT_SERVER, 'HTTP_X_REQUESTED_WITH', FILTER_DEFAULT));
        
        if ( $ajax == 'xmlhttprequest' && $type == 'POST' ) { 
            $this->_method = 'ajax-post';
        } elseif ($ajax == 'xmlhttprequest') {
            $this->_method = 'ajax';
        } else if ( in_array($type, array('POST', 'GET', 'HEAD', 'PUT', 'DELETE')) ) { 
            $this->_method = strtolower($type);
        }
    }
    
    /**
     * Devuelve el detector de dispositivo
     * @return MobileDetect
     */
    public function device() {
        if ($this->_device === NULL) {
            $this->_device = new MobileDetect();
        }
        
        return $this->_device;
    }
    
    /**
     * Devuelve la clase de manejo de sesion
     * @return Session
     */
    public function session() {
        if ($this->_session === NULL) {
            $this->_session = new Session();
        }
        
        return $this->_session;
    }
    
    /**
     * Devuelve todos losd atos enviados de un formulario
     * @return type
     */
    public function getData() {
        $values = array_filter(filter_input_array(INPUT_POST));
        foreach ($values as $key => $value) {
            if ( is_array($value) )  {
                $values[$key] = array_filter($value);
            }
        }

        return $values + $_FILES;
    }
    
    /**
     * Verifica el tipo de request dado
     * @param string $method El metodo a verificar
     * @return boolean
     */
    public function is($method) {
        $m = strtolower($method);
        if ( ($m == 'post' || $m == 'ajax') && $this->_method == 'ajax-post') {
            return TRUE;
        }
        return $m == $this->_method;
    }

    /**
     * Devuelve el dato enviado por POST
     * @param mix $name El nombre del parametro
     * @param integer $filter El filtro a utilizar
     * @return mix
     */
    public function data($name, $filter = FILTER_DEFAULT, $flag = NULL) {
        $data = $flag ? filter_input(INPUT_POST, $name, $filter, $flag) : filter_input(INPUT_POST, $name, $filter);
        return $data;
    }
    
    /**
     * Obtiene un archivo enviado al servidor
     * @param string $name El nombre del archivo
     * @return array
     */
    public function file($name) {
        return key_exists($name, $_FILES) ? $_FILES[$name] : NULL;
    }
    
    /**
     * Devuelve el dato enviado por GET
     * @param mix $name El nombre del parametro
     * @return string
     */
    public function url($name) {
        if ( key_exists($name, $this->_url) ) {
            return $this->_url[$name];
        }
        
        return htmlentities(utf8_decode(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING)));
    }

    /**
     * Devuelve las queries enviadas
     * @return array
     */
    public function getQueries() {
        return $this->_get;
    }
    
    /**
     * Devuelve la URL en array
     * @return array
     */
    public function urlToArray() {
        return $this->_url;
    }
    /**
     * Devuelve el dato de una COOKIE
     * @param string|integer $name El nombre del parametro
     * @param integer $mode El modo de desinfectacion
     * @return string
     */
    public function cookie($name) {
        if ( in_array($name, $this->cookies) ) {
            return filter_input(INPUT_COOKIE, $name, FILTER_SANITIZE_STRING);
        }
        return NULL;
    }
    
    /**
     * Devuelve el dato del SERVER
     * @param string $name El nombre del parametro
     * @return string
     */
    public function server($name) {
        return filter_input(INPUT_SERVER, $name, FILTER_SANITIZE_STRING);
    }

}