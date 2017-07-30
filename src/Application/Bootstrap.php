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
namespace PowerOn\Application;

use PowerOn\Exceptions\LogicException;
use PowerOn\Application\PowerOn;

define('PO_START_TIME', microtime(TRUE));

//Archivo de configuración de la aplicación
$config = [];
$config_file = PO_PATH_CONFIG . DS . 'application.php';

if ( is_file($config_file) ) {
    $config = include $config_file;
    if ( !is_array($config) ) {
        throw new LogicException(sprintf('El archivo (%s) debe retornar un array', $file), ['return' => $config]);
    }
}

//Creamos el framework
$poweron = new PowerOn( $config );

//Registramos el contenedor principal
$poweron->registerContainer( include POWERON_ROOT . DS . 'Application' . DS . 'Container.php' );

//Iniciamos la aplicación seleccioando el entorno adecuado
$poweron->run( DEV_ENVIRONMENT ? PowerOn::DEVELOPMENT : PowerOn::PRODUCTION );
