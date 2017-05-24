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
 * WorkdaysWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class WorkdaysWidget extends BasicWidget {

    public function __construct(array $params) {
        parent::__construct($params);
        $this->_js = array('moment.min.js', 'elements/cncservice.datetimepicker.js', 'elements/cncservice.workdays.js');
    }
    
    public function _renderElement() {
        $r = '<div  class = "' . $this->class . ' field  ' . $this->class . '" >';
            $r .= '<button class = "ui button icon labeled" id = "add_work_day"><i class = "icon plus"></i> Agregar</button>';
            $r .= '<button class = "ui button icon labeled" id = "remove_work_day" ' . ($this->default ? '' : 'disabled = "disabled"') . '>';
                $r .= '<i class = "icon remove"></i> Quitar</button>';
        $r .= '</div>';
        
        $data = $this->default ? $this->default : array(
            array('day_start' => NULL, 'day_end' => NULL, 'hour_start' => NULL, 'hour_end' => NULL)
        );

        $active = new HiddenWidget(array('name' => 'active_work_days', 'value' => $this->default ? count($this->default) : '0'));
        $r .= $active->render();
        $r .= '<div id = "work_days_container">';
        foreach ($data as $k => $d) {
            $day_start = new SelectWidget(array(
                'title' => 'DE', 'name' => 'work_days_day_start', 'multiple_name' => TRUE,
                'value' => $d['day_start'], 'disabled' => $this->default ? FALSE : TRUE,
                'options' => array(1 => 'Lunes', 2 => 'Martes', 3 => 'Mi&eacute;rcoles', 
                4 => 'Jueves', 5 => 'Viernes', 6 => 'S&aacute;bado', 0 => 'Domingo'), 'class' => 'work_day day_start fluid'
            ));
            $day_end = new SelectWidget(array(
                'title' => 'A', 'name' => 'work_days_day_end', 'multiple_name' => TRUE,
                'value' => $d['day_end'], 'disabled' => $this->default ? FALSE : TRUE,
                'options' => array('unique' => '-', 1 => 'Lunes', 2 => 'Martes', 3 => 'Mi&eacute;rcoles', 
                4 => 'Jueves', 5 => 'Viernes', 6 => 'S&aacute;bado', 0 => 'Domingo'), 'class' => 'work_day day_end fluid'
            ));
            $hour_start = new TimeWidget(array(
                'title' => 'Desde las', 'name' => 'work_days_hour_start', 'multiple' => TRUE, 'type' => 'time',
                'value' => $d['hour_start'], 'disabled' => $this->default ? FALSE : TRUE, 'class' => 'work_hour hour_start'
            ));
            $hour_end = new TimeWidget(array(
                'title' => 'Hasta las', 'name' => 'work_days_hour_end', 'multiple' => TRUE, 'type' => 'time',
                'value' => $d['hour_end'], 'disabled' => $this->default ? FALSE : TRUE, 'class' => 'work_hour hour_end'
            ));
            $r .= '<div class = "four fields ' . ($this->default ? '' : 'hidden') . '" work_days_id = "' . $k . '">';
                $r .= $day_start->render();
                $r .= $day_end->render();
                $r .= $hour_start->render();
                $r .= $hour_end->render();
            $r .= '</div>';
        }
        $r .= '</div>';
        
        return $r;
    }
    
    public function _renderField() {
        return $this->renderElement();
    }
}
