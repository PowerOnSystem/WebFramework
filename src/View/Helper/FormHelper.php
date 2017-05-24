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

namespace PowerOn\View\Helper;
use PowerOn\Form\Form;
use PowerOn\View\Widget\BasicWidget;
use  PowerOn\Routing\Router;
use PowerOn\Core\RuntimeException;

/**
 * Form
 * @author Lucas Sosa
 * @version 0.1
 */
class FormHelper {
    /**
     * Formulario
     * @var Form 
     */
    private $form;
    /** 
     * Router
     * @var Router 
     */
    private $_router;
    /**
     * Helper de html
     * @var HtmlHelper 
     */
    private $_html;

    public function __construct(Router $router, HtmlHelper $html) {
        $this->_router = $router;
        $this->_html = $html;
    }
    
    /**
     * Carga un formulario
     * @param Form $form
     */
    public function load(Form $form) {
        $this->form = $form;
        $this->form->initialize();
        $this->_html->addJs('elements/cncservice.form.js');
    }
    
    /**
     * Crea un formulario
     * @param Form $form
     * @return string
     */
    public function create(Form $form = NULL) {
        if ( !$this->form && !$form) {
            throw new RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }

        if ( $form || !$this->form ) {
            $this->load($form);
        }
        
        $r = $this->header();
        $r .= $this->response();
        $r .= $this->fields();
        $r .= $this->actions();
        $r .= $this->finish();
        return $r;
    }
    /**
     * Crea el elemento de respuesta del formulario
     * @param string $class La clase obsional del elemento
     * @return string
     * @throws RuntimeException
     */
    public function response($class = NULL) {
        if ( !$this->form ) {
            throw new RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        return '<div class = "hidden ' . $class . '" response-for = "' . $this->form->name . '"></div>';
    }
    
    /**
     * Finaliza el formulario
     * @return string
     */
    public function finish() { 
        return '</form>';
    }
    
    /**
     * Crea las acciones del formulario
     * @return string
     * @throws RuntimeException
     */
    public function actions() {
        if ( !$this->form ) {
            throw new RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        $r = '<div class = "field actions">';
        if ( $this->form->schema->actions ) {
            foreach ($this->form->schema->actions as $a) {
                $r .= $this->field($a);
            }
        } else {
            $r .= '<button id = "submit" type = "submit" disabled = "disabled" class = "ui button primary" >Aceptar</button>';
        }
        $r .= '</div>';
        
        return $r;
    }
    
    /**
     * Crea el encabezado del formulario
     * @param string $action [Opcional] El action del form
     * @param string $class [Opcional] La clase del formulario, por defecto es "inline"
     * @return string
     * @throws RuntimeException
     */
    public function header($action = NULL, $class = '') {
        if ( !$this->form ) {
            throw new RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        $options = array(
            'class' =>  'ui form ajax ' . $class,
            'name' => $this->form->name,
            'id' => $this->form->name,
            'action' => $action ? $action : $this->_router->modifyUrl(),
            'method' => 'post',
            'novalidate' => 'novalidate'
        );
        $r = '<form ' . \PowerOn\Core\PowerOnSerialize($options) . ' > ';
        $r .= '<input type = "hidden" name = "key_form" value = "' . $this->form->key . '" />';
        
        return $r;
    }
    
    /**
     * Crea todos los campos del formulario a la vez
     * @return string
     * @throws RuntimeException
     */
    public function fields() {
        if ( !$this->form ) {
            throw new RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        $r = '';
        if ( $this->form->schema ) {
            foreach ($this->form->schema->fields as $f) {
                if ( !key_exists('hidde', $f->_other) ) {
                    $r .= $this->field($f);
                }
            }
        }
        return $r;
    }
    
    /**
     * Crea un campo específico
     * @param string $field_request El campo o el nombre
     * @return string
     */
    public function field($field_request) {
        $field = $this->getField($field_request);
        return $field->render();
    }
    
    /**
     * Crea un elemento de un campo específico
     * @param string $field_request El campo o el nombre
     * @return string
     */
    public function element($field_request) {
        $field = $this->getField($field_request);
        return $field->renderElement();
    }

    /**
     * Devuelve el campo solicitado 
     * @param mix $field El nombre del campo o el objeto widget
     * @return BasicWidget
     * @throws RuntimeException
     */
    private function getField($field) {
        if ( is_string($field) && key_exists($field, $this->form->schema->fields) ) {
            $field = $this->form->schema->fields[$field];
        } elseif ( is_string($field) && key_exists($field, $this->form->schema->actions) ) {
            $field = $this->form->schema->actions[$field];
        } elseif ( !$field instanceof BasicWidget ) {
            throw new RuntimeException('El campo (' . $field . ') no existe en el formulario', 
                    array('field' => $field, 'fields' => array_keys($this->form->schema->fields)));
        }

        if ( $field->_js ) {
            foreach ($field->_js as $js) {
                $this->_html->addJs($js);
            }
        }
        if ( $field->_js ) {
            foreach ($field->_css as $css) {
                $this->_html->addCss($css);
            }
        }
        
        return $field;
    }
}
