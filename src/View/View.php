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
namespace PowerOn\View;
use PowerOn\Exceptions\DevException;
use PowerOn\Utility\Inflector;

/**
 * Description of View
 *
 * @author Lucas Sosa
 */
class View {
    /**
     * El contenido del template cargado
     * @var data 
     */
    private $content;
    /**
     * El layout principal a cargar
     * @var string
     */
    private $layout;
    /**
     * Template a cargar
     * @var array
     */
    private $template;
    /**
     * Datos cargados en la plantilla
     * @var array 
     */
    private $data = [];
    /**
     * Helpers cargados
     * @var \Pimple\Container
     */
    public $helpers;
    
    /**
     * Constructor de la clase View
     * Controla todos los templates
     */
    public function initialize() {}
    
    /**
     * Crea los Helpers por defecto del framework
     */
    public function buildHelpers(\Pimple\Container $container) {
        $this->helpers = $container;
        $this->helpers['html'] = function($c) {
            $helper = new \PowerOn\View\Helper\HtmlHelper();
            $helper->initialize($this, $c['Router'], $c['Request']);
            return $helper;
        };        
        $this->helpers['block'] = function($c) {
            $helper = new \PowerOn\View\Helper\BlockHelper();
            $helper->initialize($this, $c['Router'], $c['Request']);
            return $helper;
        };
        $this->helpers['form'] = function($c) {
            $helper = new \PowerOn\View\Helper\FormHelper();
            $helper->initialize($this, $c['Router'], $c['Request']);
            return $helper;
        };
    }
    
    /**
     * Carga un Helper
     * @param string $name El nombre a utilizar
     * @throws DevException
     */
    public function loadHelper($name) {
        $helper_file = PO_PATH_HELPER . DS . Inflector::classify($name) . 'Helper.php';
        if ( !is_file($helper_file) ) {
            throw new DevException(sprintf('No se encuentra el Helper (%s) en (%s)', $name, $helper_file));
        }
        $helper_class = 'App\View\Helper\\' . Inflector::classify($name) . 'Helper';
        
        $this->helpers[$name] = function($c) use ($helper_file, $helper_class) {
            include_once $helper_file;
         
            if ( !class_exists($helper_class) ) {
                throw new DevException(sprintf('No se encuentra la clase(%s)', $helper_class));
            }
            
            $helper = new $helper_class();
            $helper->initialize($this, $c);
            
            return $helper;
        };
    }
    
    /**
     * Renderiza la plantilla actual
     * @throws DevException
     */
    public function render() {
        $view_file = $this->template['name'] . '.phtml';
        $path = PO_PATH_TEMPLATES . DS . $this->template['folder'] . DS . $view_file;
        
        if ( !is_file($path) ) {
            throw new DevException(sprintf('No se encuentra la plantilla (%s) a cargar en (%s).', $this->template['name'], $path));
        }
        
        try {
            ob_start();
            include $path;
            $this->content = ob_get_clean();
        } catch (\RuntimeException $e) {
            //$this->content = ob_get_clean();
            throw new DevException(sprintf('Runtime Error: %s <br /><small> %s (%d)</small>', $e->getMessage(), $e->getFile(), $e->getLine()));            
        }  catch (\Exception $e) {
            ob_get_clean();
        }
        

        $path_layout = PO_PATH_TEMPLATES . DS . 'layout' . DS . ($this->layout ? $this->layout : 'default') . '.phtml';
        if ( !is_file($path_layout) ) {
            throw new DevException(sprintf('No se encuentra la plantilla principal (%s) a cargar en (%s).', $this->layout, $path_layout));
        }

        require_once $path_layout;
    }
    
    /**
     * Libera todos los datos cargados en la plantilla
     */
    public function clearData() {
        $this->data = NULL;
    }
    
    /**
     * Establece una plantilla principal a utilizar
     * @param string $name El nombre de la plantilla
     * @throws DevException
     */
    public function setLayout($name) {
        $path_layout = PO_PATH_TEMPLATES . DS . 'layout' . DS . $name . '.phtml';
        if ( !is_file($path_layout) ) {
            throw new DevException(sprintf('No existe la plantilla principal (%s) en (%s).', $name, $path_layout));
        }
        $this->layout = $name;
    }
    
    /**
     * Establece el template a utilizar
     * @param string $name Nombre del template (action)
     * @param string $folder Carpeta del modulo contenedora (controller)
     */
    public function setTemplate($name = 'index', $folder = 'index') {
        $this->template = ['name' => $name, 'folder' => $folder];
    }
    
    /**
     * Devuelve el contenido de la plantilla solicitada
     * @return string El contenido de la plantilla cargada
     */
    public function content() {
        return $this->content;
    }
    
    /**
     * Configura la salida de datos para ser tratados
     *  por el navegador en formato json
     */
    public function ajax() {        
        if ( !headers_sent() ) {
            header('Content-Type: application/json');
        }
        
        echo json_encode($this->data);
    }
    
    /**
     * Agrega una variable a pasar a la plantilla
     * @param String $name El nombre de la variable
     * @param Mix $value El valor de la variable
     */
    public function set($name, $value) {
        $this->{$name} = $value;
        return $this;
    }

    /**
     * Seteo por default de variables
     * @param string $name
     * @param mix $value
     */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }
    
    /**
     * Obtencion por default de propiedades de la plantilla
     * @param string $name
     * @return mix
     */
    public function __get($name) {
        if ( key_exists($name, $this->data) ){
            return $this->data[$name];
        } else if ( $this->helpers->offsetExists($name) ) {
            return $this->helpers[$name];
        }
        return NULL;
    }
}