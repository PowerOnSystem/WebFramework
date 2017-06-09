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
use function \PowerOn\Application\html_serialize;
/**
 * Form
 * @author Lucas Sosa
 * @version 0.1
 */
class FormHelper extends Helper {
    /**
     * Formulario
     * @var Form 
     */
    protected $_form;

    /**
     * Carga un formulario
     * @param Form $form
     */
    public function load(Form $form) {
        $this->_form = $form;
        $this->_form->initialize();
        $this->html->js('elements/cncservice.form.js');
    }
    
    /**
     * Crea un formulario
     * @param Form $form
     * @return string
     */
    public function create(Form $form = NULL) {
        if ( !$this->_form && !$form) {
            throw new \RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }

        if ( $form || !$this->_form ) {
            $this->load($form);
        }
        
        $r = $this->header();
        $r .= $this->fields();
        $r .= $this->actions();
        $r .= $this->finish();
        
        return $r;
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
     * @throws \RuntimeException
     */
    public function actions() {
        if ( !$this->_form ) {
            throw new \RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        $r = '';
        if ( $this->_form->getSchema()->fields() ) {
            foreach ($this->_form->getSchema()->fields() as $a) {
                $field = $this->_form->getSchema()->field($a);
                if ( $field['type'] == 'submit' || $field['type'] == 'button') {
                    $r .= $this->field($field);
                }
            }
        }

        return $r ? $r : '<button id="submit" type="submit">Aceptar</button>';
    }
    
    /**
     * Crea el encabezado del formulario
     * @param string $action [Opcional] El action del form
     * @param string $class [Opcional] La clase del formulario, por defecto es "inline"
     * @return string
     * @throws \RuntimeException
     */
    public function header($action = NULL, $class = '') {
        if ( !$this->_form ) {
            throw new \RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        
        $options = [
            'class' =>  $class,
            'name' => $this->_form->name,
            'id' => $this->_form->name,
            'action' => $action ? $action : $this->_router->modifyUrl(),
            'method' => 'post',
            'novalidate' => 'novalidate',
            'enctype' => 'multipart/form-data'
        ];
        $r = '<form ' . html_serialize($options) . ' > ' . PHP_EOL;
        $r .= '<input type = "hidden" name = "poweron_token" value = "' . $this->_form->getToken() . '" />' . PHP_EOL;
        
        return $r;
    }
    
    /**
     * Crea todos los campos del formulario a la vez
     * @return string
     * @throws \RuntimeException
     */
    public function fields() {
        if ( !$this->_form ) {
            throw new \RuntimeException('Debe iniciar el formulario con (FormHelper::load(Form $form))');
        }
        $r = '';
        if ( $this->_form->getSchema() ) {
            foreach ($this->_form->getSchema()->fields() as $f) {
                $field = $this->_form->getSchema()->field($f);
                if ( !key_exists('hidden', $field) ) {
                    $r .= $this->renderField($field);
                }
            }
        }
        return $r;
    }
    
    public function renderField($field) {
        $method = strtolower($field['type']) . 'RenderField';
        if ( !method_exists($this,  $method) ) {
            throw new \RuntimeException(sprintf('El campo (%s) no posee ningun renderizador.', $field['type']));
        }
        
        return $this->{ $method }( $field );
    }
    
    public function fileRenderField($field) {
        return '<input ' . html_serialize($field) . ' />';
    }
}
