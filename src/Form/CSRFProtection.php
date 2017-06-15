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
namespace PowerOn\Form;

/**
 * CSRFProtection
 * @author Lucas Sosa
 * @version 0.1
 */
class CSRFProtection {

    /**
     * Token generado
     * @var string
     */
    private $_token;
    /**
     * Request del framework
     * @var \PowerOn\Network\Request
     */
    private $_request;
    
    /**
     * Protección contra ataques CSRF
     * @param \PowerOn\Network\Request $request
     */
    public function __construct(\PowerOn\Network\Request $request) {
        $this->_request = $request;
        $this->_token = $this->_request->session()->consume('poweron_token');
    }
    
    /**
     * Genera un nuevo token
     * @param integer $length Cantidad de caracteres del token
     */
    public function generate($length = 32) {
        $this->_token = bin2hex(random_bytes($length));
        $this->_request->session()->write('poweron_token', $this->_token);
    }
    
    /**
     * Destruye el token generado
     */
    public function destroy() {
        $this->_token = NULL;
        $this->_request->session()->remove('poweron_token');
    }

    /**
     * Verifica que el token sea válido para el formulario
     */
    public function check($request_token) {
        return $this->_token === $request_token;
    }
    
    /**
     * Devuelve el token cargado
     * @return string
     */
    public function get() {
        return $this->_token;
    }
}
