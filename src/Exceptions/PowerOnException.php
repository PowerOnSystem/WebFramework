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
 * PowerOnException Controla todas las excepciones y renderiza los templates de error
 * @author Lucas Sosa
 * @version 0.1
 */
class PowerOnException extends \Exception {
    /**
     * Elementos para hacer debug del problema
     * @var array 
     */
    private $_context;
    /**
     * Descripción del problema
     * @var string
     */
    protected $_description;
    /**
     * Ayuda rápida del problema
     * @var string
     */
    protected $_help;


    /**
     * Contempla los errores de programación
     * @param string $message
     * @param array $context [Opcional] Datos para hacer debug
     * @param \Exception $exception [Opcional] Excepcion anterior
     */
    public function __construct($message = '', $code = NULL, array $context = [], \Exception $exception = NULL) {
        parent::__construct($message, $code, $exception);
        $this->_context = $context;
    }
    
    /**
     * Devuelve la información para debug
     */
    public function getContext() {
        return $this->_context;
    }
    
    /**
     * Devuelve la descripción del error
     * @return string
     */
    public function getDescription() {
        return $this->_description;
    }
    
    /**
     * Devuelve la ayuda vinculada al error
     * @return string
     */
    public function getHelp() {
        return $this->_help;
    }
    
    /**
     * Renderiza un error
     * @return string
     */
    public function getRenderedError() {
        return $this->_render();
    }
    
    /**
     * Guarda un log del error
     * @param \Monolog\Logger $logger
     */
    public function log( \Monolog\Logger $logger) {
        $reflection = new \ReflectionClass($this);
        $logger->error($this->getMessage(), [
            'type' => $reflection->getShortName(),
            'code' => $this->getCode(),
            'line' => $this->getLine(),
            'file' => $this->getFile(),
            'trace' => $this->getTrace(),
            'context' => $this->getContext()
        ]);
    }
    
    /**
     * Muestra de error por defecto
     * @return string
     */
    protected function _render() {
        return 
        '<h1>'
            . 'Error ' . $this->getCode() . ': ' . $this->getMessage()
        . '</h1>' . PHP_EOL
        . '<p>'
            . $this->getHelp()
        . '</p>';
    }
}
