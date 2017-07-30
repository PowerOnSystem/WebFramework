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
use PowerOn\Exceptions\LogicException;
use PowerOn\Exceptions\RuntimeException;
use PowerOn\Exceptions\InternalErrorException;

/**
 * View principal del framework
 * @author Lucas Sosa
 */
class View {
    /**
     * El contenido del template cargado
     * @var data 
     */
    private $_content;
    /**
     * El layout principal a cargar
     * @var string
     */
    private $_layout;
    /**
     * Template a cargar
     * @var array
     */
    private $_template;
    /**
     * Datos cargados en la plantilla
     * @var array 
     */
    private $_data = [];
    /**
     * Manager de helpers del framework
     * @var \PowerOn\Helper\HelperManager
     */
    protected $_helpers;
    /**
     * Contenedor con las clases del framework
     * @var \Pimple\Container
     */
    protected $_container;
    
    /**
     * Constructor del view del framework
     * @param \Pimple\Container $container
     */
    public function __construct(\Pimple\Container $container) {
        $this->_container = $container;
        if ( class_exists('\PowerOn\Helper\HelperManager') ) {
            $this->_helpers = new \PowerOn\Helper\HelperManager();
            
        }
    }
    
    /**
     * Constructor de la clase View
     * Controla todos los templates
     */
    public function initialize() {}

    /**
     * Renderiza la plantilla actual
     */
    public function getRenderedTemplate() {
        $template = PO_PATH_TEMPLATES . DS . $this->_template['folder'] . DS . $this->_template['name'] . '.phtml';
        $layout = $this->_layout ? PO_PATH_TEMPLATES . DS . 'layout' . DS . $this->_layout . '.phtml' : NULL;
        
        return $this->render($template, $layout);
    }
    
    /**
     * Devuelve una plantilla renderizada del framework
     * @param string $template La ubicación de la plantilla
     * @param string $layout La ubicación del layout
     */
    public function getCoreRenderedTemplate( $template, $layout = NULL ) {
        return $this->render(POWERON_ROOT . DS . $template, $layout ? POWERON_ROOT . DS . $layout : NULL);
    }
    
    /**
     * Renderiza y devuelve el contenido de la plantilla indicada
     * @param string $template Ubicación fisica de la plantilla a cargar
     * @param string $layout [Opcional] Ubicación física de la plantilla contenedora
     * @return string Devuelve la plantilla renderizada
     * @throws LogicException
     */
    private function render($template, $layout) {
        if ( !is_file($template) ) {
            throw new \InvalidArgumentException(sprintf('No se encuentra la plantilla cargar en (%s).', $template));
        }
        
        if ( $layout && !is_file($layout) ) {
            throw new \InvalidArgumentException(sprintf('No se encuentra la plantilla principal a cargar en (%s).', $layout));
        }

        //Seguridad en caso de un error fatal de programación en las plantillas
        ob_start(['PowerOn\View\View', 'handleBuffer']);
        
        try {
            include $template;
        } catch (RuntimeException $e) {
            if ( DEV_ENVIRONMENT ) {
                /* @var $logger \Monolog\Logger */
                $logger = $this->_container['Logger'];
                $logger->addDebug('Runtime Error: ' . $e->getMessage(), $e->getContext());
                
                echo $e->getRenderedError();
            } else {
                ob_end_clean();
                throw new InternalErrorException(sprintf('Runtime Error: %s <br /><small> %s (%d)</small>', 
                        $e->getMessage(), $e->getFile(), $e->getLine()));
            }
        }
        $this->_content = ob_get_clean();
        
        if ( $layout ) {
            ob_start();
            require_once $layout;
            return ob_get_clean();
        }
        
        return $this->_content;
    }
    
    /**
     * Controla el flujo de una plantilla y procesa los errores en caso de encontrarlos
     * @param string $buffer El flujo resultante
     * @return string el flujo
     */
    private function handleBuffer($buffer) {
        $error = error_get_last();

        if ( $error ) {
            $matches = [];
            preg_match('/\: (.*) in /', $error['message'], $matches);
            $message = key_exists(1, $matches) ? $matches[1] : $error['message'];
            
            /* @var $response \PowerOn\Network\Response */
            $response = $this->_container['Response'];
            
            if ( DEV_ENVIRONMENT ) {
                $response->setHeader(500);
                return 
                      '<header>'
                        . '<h1>Error: ' . $message . '</h1>'
                        . '<h2>' . $error['file'] . ' (' . $error['line'] . ')</h2>'
                    . '</header>';
            } else {
                /* @var $logger \Monolog\Logger */
                $logger = $this->_container['Logger'];
                $logger->error($message, [
                    'type' => $error['type'],
                    'file' => $error['file'],
                    'line' => $error['line']
                ]);

                /* @var $router \AltoRouter */
                $router = $this->_container['AltoRouter'];

                $response->redirect( $router->generate('poweron_error', ['error' => 500]) );
            }
        }

        return $buffer;
    }
    
    /**
     * Libera todos los datos cargados en la plantilla
     */
    public function clearData() {
        $this->_data = NULL;
    }
    
    /**
     * Establece una plantilla principal a utilizar
     * @param string $name El nombre de la plantilla
     * @throws LogicException
     */
    public function setLayout($name) {
        if ( !$name ) {
            return FALSE;
        }
        
        $path_layout = PO_PATH_TEMPLATES . DS . 'layout' . DS . $name . '.phtml';
        if ( !is_file($path_layout) ) {
            throw new LogicException(sprintf('No existe la plantilla principal (%s) en (%s).', $name, $path_layout));
        }
        $this->_layout = $name;
    }
    
    /**
     * Establece el template a utilizar
     * @param string $name Nombre del template (action)
     * @param string $folder Carpeta del modulo contenedora (controller)
     */
    public function setTemplate($name = 'index', $folder = 'index') {
        $this->_template = ['name' => $name, 'folder' => $folder];
    }
    
    /**
     * Devuelve el contenido de la plantilla solicitada
     * @return string El contenido de la plantilla cargada
     */
    public function content() {
        return $this->_content;
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
        $this->_data[$name] = $value;
    }
    
    /**
     * Obtencion por default de propiedades de la plantilla
     * @param string $name
     * @return mix
     */
    public function __get($name) {
        if ( key_exists($name, $this->_data) ){
            return $this->_data[$name];
        } else if ( $this->_helpers !== NULL && $this->_helpers->helperExist($name) ) {
            return $this->_helpers->getHelper($name);
        }
        return NULL;
    }
}