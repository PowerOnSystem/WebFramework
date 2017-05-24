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
class ImageWidget extends BasicWidget {
 
    /**
     * Adjuntos a editar
     * @var \CNCService\Attachment\Entities\Attachment
     */
    public $default;
    
    public function __construct(array $params) {
        parent::__construct($params);
        $this->_js = array('elements/cncservice.image.js');
    }
    
    public function _renderElement() {
        $r = '<div id = "image_container" class = "' . $this->class . '">' ;
            $r .= '<div class = "ui action input">';
                $r .= '<label class = "ui labeled left icon button fluid" id = "image_input">';
                    $r .= '<i class = "file icon"></i>';
                    $r .= '<span>Explorar</span>';
                    $r .= '<input type = "file" id = "image" name = "image' . ($this->multiple ? '[]' : '') . '"'
                            . ' class = "hidden" ' . ($this->multiple ? 'multiple = "multiple"' : '') . ' placeholder = "Im&aacute;genes" />';
                $r .= '</label>';
            $r .= '</div>';
            if ( $this->_edition_mode ) {
                $r .= '<div class = "ui middle aligned divided list">';
                $options = array();
                if ($this->default) {
                    foreach ( $this->default as $attachment) {
                        $options[] = '<option value = "' . $attachment->id . '" selected>' . $attachment->id . '</option>';
                        $r .= '<div class = "item" image-container = "' . $attachment->id . '" >';
                            $r .= '<div class = "right floated content">';
                                $r .= '<div class = "ui icon button" '
                                        . 'onclick = "maximize_image(\'' . $attachment->location . '/' . $attachment->file . '\')">'
                                        . '<i class = "icon zoom in"></i>'
                                    . '</div>';
                                $r .= '<div class = "ui icon button red" image-id = "' . $attachment->id . '" ><i class = "icon remove"></i></div>';
                            $r .= '</div>';
                            $r .= '<img class = "ui avatar image" src = "' . $attachment->location . '/thumbs/' . $attachment->file . '"'. ' />';
                            $r .= '<div class = "content">';
                                $r .= $attachment->file;
                            $r .= '</div>';
                        $r .= '</div>';
                    }
                }
                $r .= '</div>';
                $r .= '<select class = "hidden imagedefault" name = "' . $this->_real_name . '_default[]" id = "' . $this->_real_name . '_default" multiple = "multiple">' . implode('\n', $options) . '</select>';
            }
        $r .= '</div>';
        
        return $r;
    }
}
