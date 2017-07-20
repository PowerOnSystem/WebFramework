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
use PowerOn\Exceptions\LogicException;

/**
 * Controlador avanzado con más herramientas
 * @version 0.1
 * @property \PowerOn\View\View $view Controlador del template
 * @property \PowerOn\Network\Request $request Datos de la solicitud del cliente
 * @property \Monolog\Logger $logger Creador de archivos log
 * @property \PowerOn\Database\Database $database Modulo PowerOn/DataBaseService
 * @property \PowerOn\Network\Response $response Respuesta del servidor
 * @author Lucas
 */
class Controller {
    /**
     * Contenedor principal
     * @var \Pimple\Container
     */
    private $_container;
    /**
     * Inicializa un controlador
     * @param \Pimple\Container Contenedor de Pimple
     */
    public function registerContainer(\Pimple\Container $container) {
        $this->_container = $container;
    }
    
    public function __get($name) {
        $object_name = \PowerOn\Utility\Inflector::classify($name);
        if ( $this->_container->offsetExists($object_name) ) {
            $object = $this->_container[$object_name];
            if ( !$object ) {
                throw new LogicException(sprintf('El m&oacute;dulo (%s) solicitado no fue instalado.', $object_name));
            }
            return $object;
        }
    }
}