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
 * HiddenWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class FileWidget extends BasicWidget {
    /**
     * Si el campo posee descripción
     * @var boolean
     */
    public $_description;
    /**
     * Adjuntos a editar
     * @var \CNCService\Attachment\Entities\Attachment
     */
    public $default;
    
    public function __construct(array $params) {
        $this->_description = key_exists('description', $params) && $params['description'] ? $params['description'] : FALSE;
        parent::__construct($params);
        $this->_js = array('elements/cncservice.attach.js');
    }
    
    public function _renderElement() {
        $r = '<div id = "attachment_container" class = "' . $this->class . '">';
            $r .= '<div class = "fields attachment_field" attach-number = "0" >';
            if ( $this->_description ) {
                $r .= '<div class = "five wide field">'
                        . '<label class = "label">Descripci&oacute;n</label>'
                        . '<div class = "input">'
                            . '<input type = "text" class = "attachment_description" value = "' . 
                                ($this->_description !== TRUE ? $this->_description : '') . '"'
                                . ' name = "attachment_description' . ($this->multiple ? '[]' : '') . '">'
                        . '</div>'
                    . '</div>';
            }
                $r .= '<div class = "' . ($this->_description ? 'three' : 'five') . ' wide field">';
                    $r .= '<label class = "label">N&uacute;mero</label>';
                    $r .= '<div class = "input">';
                        $r .= '<input type = "text" class = "attachment_number" ' 
                            . 'name = "attachment_number' . ($this->multiple ? '[]' : '') . '">';
                    $r .= '</div>';
                $r .= '</div>';
                $r .= '<div class = "' . ($this->_description ? 'five' : 'eight') . ' wide field">';
                    $r .= '<label for = "attachment" class = "label">Archivo(s)</label>';
                    $r .= '<div class = "ui action input">';
                        $r .= '<label class = "ui labeled left icon button fluid ">';
                                $r .= '<i class = "file icon"></i>';
                                $r .= '<span>Explorar</span>';
                                $r .= '<input type = "file" id = "attachment" '
                                    . 'name = "attachment' . ($this->multiple ? '[]' : '')
                                    . '" class = "hidden attachment_file" ' . ($this->multiple ? 'multiple = "multiple"' : '')
                                    . ' attach-number = "0" placeholder = "Adjuntos" />';
                        $r .= '</label>';
                    $r .= '</div>';
                $r .= '</div>';
                $r .= '<div class = "three wide field">';
                    $r .= '<label class = "label">&nbsp;</label>';
                    $r .= '<div class = "ui icon button cnc right aligned disabled attachment_add" attach-number = "0">';
                        $r .= '<i class = "icon plus"></i>';
                    $r .= '</div>';
                    $r .= '<div class = "ui icon button cnc right aligned disabled attachment_remove" attach-number = "0">';
                        $r .= '<i class = "icon remove"></i>';
                    $r .= '</div>';
                $r .='</div>';
            $r .= '</div>';
        if ( $this->default ) {
            $options = array();
            $r .= '<div class = "ui middle aligned divided list">';
            foreach ($this->default as $attachment) {
                $options[] = '<option value = "' . $attachment->id . '" selected>' . $attachment->id . '</option>';
                $r .= '<div class = "item" attachment-container = "' . $attachment->id . '" >';
                    $r .= '<div class = "right floated content">';
                        $r .= '<a href = "' . $attachment->location . '/' . $attachment->file . '" target = "_blank" class = "ui icon button"> '
                                . '<i class = "icon search"></i>'
                            . '</a>';
                        $r .= '<div class = "ui icon button red" attach-id = "' . $attachment->id . '" ><i class = "icon remove"></i></div>';
                    $r .= '</div>';
                    $r .= '<i class = "icon big file" ></i>';
                    $r .= '<div class = "content">';
                        $r .= $attachment->description;
                    $r .= '</div>';
                $r .= '</div>';
            }
            $r .= '</div>';
            $r .= '<select class = "hidden" name = "attachment_default[]" id = "attachment_default" multiple = "multiple">' . implode('\n', $options) . '</select>';
        }
        $r .= '</div>';
        
        return $r;
    }
}
