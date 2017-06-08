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
use PowerOn\Utility\Lang;

/**
 * ProdException Controla los errores definidos para el usuario final
 * @author Lucas Sosa
 * @version 0.1
 */
class ProdException extends \Exception {
    
    private $debug_message;
    /**
     * Contempla los errores de programación
     * @param integer $code Código de error
     * @param string $message [Opcional] Mensaje a enviar al desarrollador
     * @param \Exception $exception [Opcional] Excepcion anterior
     */
    public function __construct($code = NULL, $message = '', \Exception $exception = NULL) {
        $this->debug_message = $message;
        $message = Lang::get('errors.' . $code);

        parent::__construct($message ? $message : 'No es posible continuar con la operaci&oacute;n', $code, $exception);
    }
}
