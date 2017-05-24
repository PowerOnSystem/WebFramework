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
class CheckboxWidget extends BasicWidget {
    
    public $checked;
    
    public function __construct(array $params) {
        parent::__construct($params);
    }

    public function _renderField() {
        $this->checked = $this->value ? 'checked' : NULL;
        
        $r = '<div field-for = "' . $this->name . '" class = "ui checkbox ' . ($this->class ? $this->class : '') . ' field">' . PHP_EOL;
            $r .= '<label for = "' . $this->name . '" class = "label">' . PHP_EOL;
                $r .= key_exists('required', $this->_rules) ? 
                        ' <span class = "cnc red text" title = "Campo obligatorio">*</span> ' . PHP_EOL : '';
                $r .= $this->_title . PHP_EOL;
            $r .= '</label>' . PHP_EOL;
                $r .= $this->renderElement();
        $r .= '</div>' . PHP_EOL;
        
        return $r;
    }
}
