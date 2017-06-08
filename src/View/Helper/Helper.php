<?php

/*
 * Copyright (C) PowerOn Sistemas
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

namespace PowerOn\View\Helper;
use PowerOn\View\View;

/**
 * Helper Contenedor de helpers del framework
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class Helper {
    /**
     * Router del framework
     * @var \PowerOn\Routing\Router
     */
    protected $_router;
    /**
     * Request del framework
     * @var \PowerOn\Network\Request
     */
    protected $_request;
    /**
     * Instancia de view donde se utiliza el helper
     * @var View
     */
    private $_view;
    
    public function initialize(View $view) {
        $this->_view = $view;
    }
    
    /**
     * Cargador directo de helpers
     * @param string $name Nombre del helper a cargar
     * @return Helper|null Si se encuentra un helper con ese nombre lo retorna.
     */
    public function __get($name) {
        if ( !isset( $this->{$name} )  && $this->_view->helpers->offsetExists($name) ) {
            $this->{$name} = $this->_view->helpers[$name];
            
            return $this->{$name};
        }
    }
}
