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
 * EmailWidget
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class EmailWidget extends BasicWidget {

    public function __construct(array $params) {
        parent::__construct($params);
    }
    
    public function _renderElement() {
        $r = '<div class = "ui input left action">';
            $r .= '<button class = "ui button" onclick = "$(\'#' . $this->id . '\').val($(\'#' . $this->id . '\').val() + \'@\').focus(); return false;">@</button>';
            $r .= '<input ' . $this->serialize() . ' />';
        $r .= '</div>';
        
        return $r;
    }
}
