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
/**
 * Console consola con acciones del package
 * @version 0.1
 * @author Lucas
 */
class Console {
    /**
     * Establece el entorno de trabajo en modo produccion al 
     * solicitarlo como requisito en composer
     */
    public static function postInstall() {
        $self = dirname(__FILE__);
        $configFile = $self . DIRECTORY_SEPARATOR . 'DefaultConfiguration.php';
        $arrayFile = file($configFile);
        $position = array_search("define('PO_DEVELOPER_MODE', TRUE);" . PHP_EOL, $arrayFile);
        if ( $position !== FALSE ) {
            $arrayFile[$position] = "define('PO_DEVELOPER_MODE', FALSE);" . PHP_EOL;
            $file = fopen($configFile, 'w');
            fwrite($file, implode('', $arrayFile));
        }
    }
}
