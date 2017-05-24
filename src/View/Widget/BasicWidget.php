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

namespace CNCService\View\Widget;
use CNCService\Utility\Sanitize;
/**
 * BasicWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class BasicWidget {
    
    protected $_title;
    protected $_params;
    protected $_rules;
    protected $_multiple_name;
    protected $_field_class;
    protected $_real_name;
    protected $_edition_mode;
    
    public $_sanitize;
    public $_filter;
    public $_flag;
    public $_other;
    public $_js = array();
    public $_css = array();
    
    public $name;
    public $placeholder;
    public $id;
    public $class;
    public $disabled;
    public $multiple;
    public $required;
    public $value;
    public $default;
    
    public function __construct(array $params) {
        $this->_title = key_exists('title', $params) ? $params['title'] : NULL;
        $this->_rules = key_exists('rules', $params) ? $params['rules'] : array();
        $this->_multiple_name = key_exists('multiple_name', $params) ? TRUE : FALSE;
        $this->_field_class = key_exists('field_class', $params) ? $params['field_class'] : '';
        $this->_edition_mode = key_exists('edition_mode', $params) ? $params['edition_mode'] : FALSE;
        
        $this->type = key_exists('type', $params) ? $params['type'] : NULL;
        $this->name = key_exists('name', $params) ? $params['name'] : NULL;
        
        $this->placeholder = key_exists('placeholder', $params) ? $params['placeholder'] : NULL;
        $this->id = key_exists('id', $params) ? $params['id'] : $this->name;
        $this->class = key_exists('class', $params) ? $params['class'] : NULL;
        $this->disabled = key_exists('disabled', $params) && $params['disabled'] ? 'disabled' : NULL;
        $this->multiple = key_exists('multiple', $params) && $params['multiple'] ? 'multiple' : NULL;
        $this->required = key_exists('required', $params) && $params['required'] ? 'required' : NULL;
        $this->value = key_exists('value', $params) ? $params['value'] : NULL;
        $this->default = $this->value;
        
        $this->_filter = key_exists('filter', $params) ? $params['filter'] : FILTER_SANITIZE_STRING;
        $this->_flag = key_exists('flag', $params) ? $params['flag'] : 
            ($this->multiple && $this->type != 'select' ? FILTER_REQUIRE_ARRAY : NULL);
        $this->_sanitize = key_exists('sanitize', $params) ? $params['sanitize'] : 
            ($this->type == 'date' ? Sanitize::SANITIZE_DATE_TIME : (
                    $this->type == 'number' ? Sanitize::SANITIZE_NUMBER : Sanitize::SANITIZE_STRING));
        $this->_other = array_diff_key($params, 
            array_fill_keys(array(
                    'type', 'title', 'default', 'placeholder', 'name', 'field_class', 'multiple_name', 'value',
                    'class', ' disabled', 'options', 'sanitize'
                ), NULL
            ) 
        );
        $this->_real_name = $this->name;
        
        $ref = new \ReflectionClass($this);
        $this->name .= $this->multiple && $ref->getName() != 'CNCService\View\Widget\SelectWidget' ? '[]' : '';
    }
    
    public function render() {
        if ( !method_exists($this, '_renderField') ) {
            return $this->defaultField();
        }
        
        return $this->_renderField();
    }
    
    public function renderElement() {
        if ( !method_exists($this, '_renderElement') ) {
            return $this->defaultElement();
        }
        return $this->_renderElement();
    }
    
    private function defaultField() {
        $r = '<div field-for = "' . $this->_real_name . '" class = "field ' . 
                (key_exists('required', $this->_rules) ? 'required' : '') . ' ' . $this->_field_class . '">' . PHP_EOL;
            $r .= '<label for = "' . $this->_real_name . '" class = "label">' . PHP_EOL;
                $r .= $this->_title . PHP_EOL;
            $r .= '</label>' . PHP_EOL;
                $r .= $this->renderElement();
                $r .= '<div class = "cnc info_container"></div>';
        $r .= '</div>' . PHP_EOL;
        
        return $r;
    }
    
    private function defaultElement() {
        $r = '<input ' . $this->serialize() . ' ' . ($this->_other ? $this->serialize('_other') : '') . '/>';
        return $r;
    }
    
    protected function serialize($params = NULL) {
        
        $result = array();
        if ( !$params ) {
            $widget_params = !$params ? get_object_vars($this) : (!is_array($params) ? array($params) : $params);
            
            foreach ($widget_params as $name => $value) {
                if (substr($name, 0, 1) != '_' && $value !== NULL) {
                    $result[$name] = $value; 
                }
            }
        } else {
            $widget_params = !is_array($params) ? array($params) : $params;
            foreach ($widget_params as $property) {
                if ( property_exists($this, $property) ) {
                    if ( !is_array($this->{ $property }) ) {
                        $result[$property] = $this->{ $property };
                    } else {
                        $result += $this->{ $property };
                    }
                }
            }
        }

        return \CNCService\Core\CNCServiceSerialize($result);
    }
    
    public function setRules(array $rules) {
        $this->_rules = $rules;
    }
}
