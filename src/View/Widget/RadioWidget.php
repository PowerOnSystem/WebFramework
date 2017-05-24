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
 * RadioWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class RadioWidget extends BasicWidget {
    
    public $_options;
    
    public function __construct(array $params) {
        $this->_options = key_exists('options', $params) ? $params['options'] : array();
        parent::__construct($params);
    }

    public function _renderElement() {        
        $this->class .= ' ui toggle checkbox ';
        $r = '<div class = "ui grid equal width">';
        foreach ($this->_options as $value => $text) {
            $r .= '<div class = "column">';
                $r .= '<div ' . $this->serialize() . '>';
                    $r .= '<input value = "' . $value . '" class = "hidden " type = "radio" ' . 
                        ($this->value != NULL && $this->value == $value ? 'checked' : '') .
                            ' name = "' . $this->name . '" id = "' . $this->id . '_' . $value . '" >';
                    $r .= '<label>' . $text . '</label>';
                $r .= '</div>';
            $r .= '</div>';
        }
        
        $r .= '</div>';
        
        return $r;
    }
}
