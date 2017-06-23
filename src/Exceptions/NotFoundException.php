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
 * NotFoundException Excepciones producidas por falta la falta de algúna variable o archivo importante
 * @author Lucas Sosa
 * @version 0.1
 */
class NotFoundException extends PowerOnException {
    
    private $_message = 'El sector al que intenta ingresar no existe.';
    /**
     * Contempla los errores de programación
     * @param string $message
     * @param array $context [Opcional] Datos para hacer debug
     * @param \Exception $exception [Opcional] Excepcion anterior
     */
    public function __construct($message = '', array $context = [], \Exception $exception = NULL) {
        parent::__construct($message ? $message : $this->_message, 404, $context, $exception);
    }
    
    
    protected function _render() {
        return 
        '<!DOCTYPE html>'
        . '<html>' . PHP_EOL
            . '<head>' . PHP_EOL
                . '<title>Error 404</title>' . PHP_EOL
            . '</head>' . PHP_EOL
            . '<body>' . PHP_EOL
                . '<h1>&iexcl;Ups!: '
                . $this->getMessage()
                . '</h1>' . PHP_EOL
                . '<p>'
                . $this->getHelp()
                . '</p>' . PHP_EOL
            . '</body>' . PHP_EOL
        . '</html>';
    }
}
