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
namespace PowerOn\Exceptions;

/**
 * RuntimeException Excepciones internas para modo producción
 * @author Lucas Sosa
 * @version 0.1
 */
class RuntimeException extends PowerOnException {
    
    private $_message = 'Error en tiempo de ejecuci&oacute;n.';

    /**
     * Contempla los errores de programación
     * @param string $message
     * @param \Exception $exception [Opcional] Excepcion anterior
     */
    public function __construct($message = NULL) {
        parent::__construct($message ? $message : $this->_message);
    }
    
    public function _render() {
        return 
            '<div style=" border: 1px lightgray solid; padding:10px; background-color: #d3d3d3;">'
                . '<span style="color:brown;font-weight:bold;">'
                    . 'Runtime Error:'
                . '</span> ' 
                . $this->getMessage() 
                . '<br />'
                . '<span style="color: gray">' 
                    . $this->getFile()
                . ' - </span>'
                . '<span style="color: salmon">' 
                    . $this->getLine()
                . '</span>'
            . '</div>';
    }
}
