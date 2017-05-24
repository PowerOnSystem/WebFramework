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
use CNCService\Utility\Moment;

/**
 * RadioWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class TimeWidget extends BasicWidget {
    
    public $date;
    
    public function __construct(array $params) {
        parent::__construct($params);
        $this->_js = array('moment.min.js', 'elements/cncservice.datetimepicker.js');
    }

    public function _renderElement() {
        $m = new Moment($this->value);
        $this->value = $m->isValid() ? $m->format(CNC_TIME_FORMAT) : '';
        $this->default = $this->value;
        $this->date = $m->format();
        
        $r = '<div class = "ui left icon input">';
            $r .= '<input ' . $this->serialize() . '/>';
            $r .= '<i class = "icon clock"></i>';
        $r .= '</div>';
        
        return $r;
    }
}
