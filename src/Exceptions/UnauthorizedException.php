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
 * UnauthorizedException Excepciones de error de autorización
 * @author Lucas Sosa
 * @version 0.1
 */
class UnauthorizedException extends PowerOnException {
    
    private $_message = 'No esta autorizado a ingresar en este sector.';
    
    /**
     * Contempla los errores de programación
     * @param string $message
     * @param array $context [Opcional] Datos para hacer debug
     * @param \Exception $exception [Opcional] Excepcion anterior
     */
    public function __construct($message = NULL, \Exception $exception = NULL) {
        parent::__construct($message ? $message : $this->_message, NULL, [], $exception);
    }
}
