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
 * PhoneWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class PhoneWidget extends BasicWidget {
    /**
     * Adjuntos a editar
     * @var array
     */
    public $default;
    
    public function __construct(array $params) {
        parent::__construct($params);
        $this->_js = array('elements/cncservice.phone.js');
    }
    
    public function _renderElement() {
        $data = $this->default ? $this->default : array(
            array('code' => '', 'number' => '', 'type' => '')
        );
        $active = new HiddenWidget(array('name' => 'active_phones', 'value' => $this->default ? count($this->default) : '1'));
        
        $r = '<div id = "phone_container" class = "' . $this->class . '"  >';
        $r .= $active->render();
        foreach ($data as $key => $phone) {
            $code = new BasicWidget(array(
                'title' => 'C.A.', 'placeholder' => '011', 'name' => 'phone_code', 'multiple' => TRUE,
                'class' => 'phone_code', 'type' => 'tel', 'value' => $phone['code'], 'field_class' => 'two wide'
            ));
            $number = new BasicWidget(array(
                'title' => 'N&uacute;mero', 'placeholder' => 'Ej: 47304447', 'name' => 'phone_number', 'multiple' => TRUE,
                'class' => 'phone_number', 'type' => 'tel', 'value' => $phone['number'], 'field_class' => 'four wide'
            ));
            $type = new SelectWidget(array(
                'title' => 'Tipo', 'name' => 'phone_type', 'value' => $phone['type'], 'multiple_name' => $this->multiple ? TRUE : FALSE,
                'placeholder' => 'Tipo de tel&eacute;fono', 'options' => array(
                    'phone' => 'Tel&eacute;fono Fijo', 'fax' => 'Fax', 'cellphone' => 'Celular'
                ), 'field_class' => 'seven wide', 'class' => 'phone_type'
            ));
            
            $r .= '<div class = "fields phone_field" phone-number = "' . $key . '" >';
                $r .= $code->render();
                $r .= $number->render();
                $r .= $type->render();
                $r .= '<div class = "three wide field">';
                    $r .= '<label class = "label">&nbsp;</label>';
                    $r .= '<div class = "ui icon button cnc right aligned '
                            . ( count($data) == $key + 1 ? '' : 'disabled') . ' phone_add" phone-number = "' . $key . '">';
                        $r .= '<i class = "icon plus"></i>';
                    $r .= '</div>';
                    $r .= '<div class = "ui icon button cnc right aligned ' 
                            . ($phone != '' ? '' : 'disabled') . ' phone_remove" phone-number = "' . $key . '">';
                        $r .= '<i class = "icon remove"></i>';
                    $r .= '</div>';
                $r .='</div>';
            $r .= '</div>';
        }
            
        $r.= '</div>';
        
        return $r;
    }
}
