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
     * Copia todos los elementos de una carpeta en otra
     * @param string $src Carpeta donde se encuentran los archivos a copiar
     * @param string $dst Carpeta destino donde se van a copiar los archivos
     */
    protected static function recurseCopy($src, $dst) {
        $dir = opendir($src);
        if ( !is_dir($dst) && !mkdir($dst) ) {
            
        }
        
        while( ($file = readdir($dir)) !== FALSE ) {
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    self::recurseCopy($src . '/' . $file, $dst . '/' . $file); 
                } else { 
                    copy($src . '/' . $file,$dst . '/' . $file); 
                }
            }
        }
        closedir($dir); 
    }
}
