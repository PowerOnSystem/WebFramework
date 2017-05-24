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
     * Datos cargados en la plantilla
     * @var array 
     */
    private $data = [];
    /** 
     * Ayudante de HTML
     * @var Helper\HtmlHelper 
     */
    public $html;
    /**
     * Ayudante de Formularios
     * @var Helper\FormHelper 
     */
    public $form;
    /**
     * Ayudante de Bloques de codigo
     * @var Helper\BlockHelper 
     */
    public $block;
    /**
     * Errores
     * @var array 
     */
    public $errors;
    /**
     * Alertas
     * @var array 
     */
    public $warnings;
    /**
     * Template a cargar
     * @var array
     */
    public $template;

    /**
     * Constructor de la clase View
     * Controla todos los templates
     */
    public function __construct(Helper\HtmlHelper $htmlHelper, Helper\FormHelper $form, Helper\BlockHelper $block) {
        $this->html = $htmlHelper;
        $this->form = $form;
        $this->block = $block;
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
     * Carga en pantalla la plantilla indicada
     * @throws CoreException En caso de error
     */
    public function render() {
        $view_file = $this->template['name'] . '.phtml';
        $path = PO_PATH_TEMPLATES . DS . $this->template['folder'] . DS . $view_file;
        
        if ( !is_file($path) ) {
            throw new \Exception(sprintf('No se encuentra la plantilla (%s) a cargar en (%s).', $this->template['name'], $path));
        }
        
        try {
            ob_start();
            include $path;
            $this->content = ob_get_clean();
        } catch (\RuntimeException $e) {
            ob_end_clean();
            throw new \Exception(sprintf('Runtime Error: %s <br /><small> %s (%d)</small>', $e->getMessage(), $e->getFile(), $e->getLine()));
            
        }

        $path_layout = PO_PATH_TEMPLATES . DS . 'layout' . DS . ($this->layout ? $this->layout : 'default') . '.phtml';
        if ( !is_file($path_layout) ) {
            throw new \Exception(sprintf('No se encuentra la plantilla principal (%s) a cargar en (%s).', $this->layout, $path_layout));
        }

        require_once $path_layout;
    }
    
    public function clearData() {
        $this->data = NULL;
    }
    /**
     * Establece una plantilla principal a utilizar
     * @param String $name El nombre de la plantilla
     * @throws CoreException
     */
    public function changeLayout($name) {
        $path_layout = PO_DIR_APP . DS . 'layout' . DS . $name . '.phtml';
        if ( !is_file($path_layout) ) {
            throw new CoreException('No existe la plantilla solicitada (' . $name . ').', array('path' => $path_layout));
        }
        $this->layout = $name;
    }
    
    /**
     * Devuelve el contenido de la plantilla solicitada
     * @return HtmlHelper El contenido de la plantilla cargada
     */
    public function content() {
        return $this->content;
    }
    
    /**
     * Configura la salida de datos para ser tratados
     *  por el navegador en formato json
     */
    public function ajax() {
        $data = array(
            'data' => $this->data,
            'errors' => $this->errors,
            'warning' => $this->warnings
        );
        if ( !headers_sent() ) {
            header('Content-Type: application/json');
        }
        echo json_encode($data);
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
    
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }
    
    public function __get($name) {
        if ( key_exists($name, $this->data) ){
            return $this->data[$name];
        }
        return NULL;
    }
    
    public function error(array $error, $type = NULL) {
        $data = $error + array(
            'code' => 0,
            'title' => '',
            'message' => '',
            'help_title' => '',
            'help_content' => '',
            'debug' => [],
            'trace' => []
        );
        $this->errors[$type ? $type : count($this->errors)] = $data;
    }
    
    public function warning(array $warning) {
        $data = $warning + array(
            'title' => '',
            'message' => '',
            'allow_edit' => FALSE
        );
        $this->warnings[] = $data;
    }
    
    public function setErrors(array $errors) {
        $this->errors = $errors;
    }
    
    public function redirect($url) {
        $this->clearData();
        $this->set('redirect', $url);
    }
    
    public function formError(array $errors, $type = 'validation') {
        $this->clearData();
        if ($errors) {
            $this->errors[$type] = $errors;
        }
    }
    
    public function formWarning(array $warnings) {
        if ($warnings) {
            $this->warnings = $warnings;
        }
    }
}