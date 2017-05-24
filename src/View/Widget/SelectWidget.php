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

/**
 * SelectWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class SelectWidget extends BasicWidget {
    
    public $_options;
    
    public function __construct(array $params) {
        $this->_options = key_exists('options', $params) && is_array($params['options']) ? $params['options'] : array();
        parent::__construct($params);
    }
    
    protected function _renderElement() {
        $selected = $this->value ? (is_array($this->value) ? $this->value : array($this->value)) : array();
        
        $this->class .= ' ui dropdown selection ' . $this->multiple . ' ' . ($this->disabled ? 'disabled' : '');
        $r = '<div ' . $this->serialize(array('id', 'class', 'required')) . ' default = "' . $this->default . '">' . PHP_EOL;
            $r .= '<i class = "icon ' . (preg_match('/search/', $this->class) ? 'search' : 'dropdown') . '"></i>';

            $r .= '<div class = "' . 
                ( $selected && !$this->multiple && !array_diff($selected, array_keys($this->_options)) ? '' : 'default')
                    . ' text">' . $this->placeholder . '</div>';
            $r .= '<input type = "hidden" name = "' . $this->name . ($this->_multiple_name ? '[]' : '') . '" value = "' . 
                    implode(',', $selected)  . '" default = "' . $this->default . 
                    '" ' . $this->serialize('_other') . ' />' . PHP_EOL;
            if ( $this->_options ) {
                $r .= '<div class = "menu">';
                foreach ($this->_options as $value => $title) {
                    $r .= '<div class = "item" data-value = "' . $value . '" >' . $title . '</div>' . PHP_EOL;
                }
                $r .= '</div>';
            }
        $r .= '</div>' . PHP_EOL;
        
        return $r;
    }

}
