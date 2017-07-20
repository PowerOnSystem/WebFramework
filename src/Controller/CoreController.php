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

namespace PowerOn\Controller;
use PowerOn\Utility\Config;
/**
 * CoreController controlador de funciones por defecto
 * @author Lucas Sosa
 * @version 0.1
 */
class CoreController extends Controller {

    /**
     * Sección de muestra de errores por defecto del framework
     */
    public function error() {
        $error = $this->request->url(2);
        $error_layout = Config::get('Error.layout');
        $this->view->setLayout($error_layout ? $error_layout : 'error');
        
        if ( in_array($error, [500, 404]) ) {
            $this->response->defaultHeader($error);
            $this->view->setTemplate( is_file(PO_PATH_TEMPLATES . DS . 'error' . DS . 'error-' . $error . '.phtml') ? 
                    'error-' . $error : 'default', 'error');
        } else {
            $this->view->setTemplate('default', 'error');
        }
    }

}
