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
namespace PowerOn\Utility;
use PowerOn\Exceptions\DevException;

/**
 * Config
 * @author Lucas Sosa
 * @version 0.1
 */
class Config {

    private static $_config;
    /**
     * Inicializa la configuración
     * @param string $file Ruta del archivo de configuración
     * @throws DevException
     */
    public static function initialize($file) {
        if ( !is_file($file) ) {
            throw new DevException(sprintf('No se encuentra el archivo (%s)', $file));
        }
        $config = include $file;
        if ( !is_array($config) ) {
            throw new DevException(sprintf('El archivo (%s) debe retornar un array', $file), ['return' => $config]);
        }
        self::$_config = $config;
    }
    
    /**
     * Setea un nuevo valor de configuración
     * @param string $name El nombre de la configuración
     * @param mix $value Su valor
     */
    public static function set($name, $value) {
        Hash::insert(self::$_config, $name, $value);
    }
    
    /**
     * Devuelve un valor de configuración
     * @param string $name El nombre de la variable
     * @return mix El valor solicitado o NULL caso contrario
     */
    public static function get($name) {
        return key_exists($name, self::$_config) ? Hash::get(self::$_config, $name) : NULL;
    }
    
    /**
     * Verifica si una configuración existe
     * @param string $name
     * @return boolean
     */
    public static function exist($name) {
        return Hash::check(self::$_config, $name);
    }
}
