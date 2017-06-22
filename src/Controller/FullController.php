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

/**
 * Controlador avanzado con más herramientas
 * @version 0.1
 * @author Lucas
 */
class FullController {
    /**
     * Control del template
     *  @var \PowerOn\View\View 
     */
    protected $view;
    /**
     * Todos los datos de la solicitud del cliete
     *  @var \PowerOn\Network\Request
     */
    protected $request;
    /**
     * Creador de archivos log
     * @var \Monolog\Logger
     */
    protected $logger;
    /**
     * Registra una excepcion generada
     * @var \Exception
     */
    protected $exception;
    /**
     * Contenedor principal
     * @var \Pimple\Container
     */
    private $_container;
    /**
     * Inicializa un controlador
     * @param View $view
     * @param Request $request
     * @param Router $router
     * @param \Monolog\Logger $logger
     */
    public function registerServices(\Pimple\Container $container) {
        $this->_container = $container;
    }
    
    public function registerException(\Exception $exception) {
        $this->exception = $exception;
    }
    
    public function __get($name) {
        if ( $this->_container->offsetExists(\PowerOn\Utility\Inflector::classify($name)) ) {
            echo 'existe';
        } else echo 'no existe';
    }
}