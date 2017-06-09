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

namespace PowerOn\Form;

/**
 * Schema
 * @author Lucas Sosa
 * @version 0.1
 */
class Schema {
    /**
     * Campos del esquema
     * @var array 
     */
    private $_fields = [];
    /**
     * Atributos por defecto de cada campo
     * @var array
     */
    private $_default = [
        'name' => NULL,
        'type' => NULL,
        'sanitize' => NULL
    ];
    
    /**
     * Agrega un campo nuevo
     * @param string $name Nombre
     * @param array $request_attrs Los parámetros del campo
     * @return Schema
     */
    public function add( $name, $request_attrs = [] ) {
        if ( is_string($request_attrs) ) {
            $request_attrs = ['type' => $request_attrs];
        }
        $request_attrs['name'] = $name;
        
        $attrs = array_intersect_key($request_attrs, $this->_default);
        $this->_fields[$name] = $attrs + $this->_default;
        
        return $this;
    }
    
    /**
     * Devuelve un campo solicitado
     * @param string $name Nombre del campo requerido
     * @return array El campo o NULL si no existe
     */
    public function field($name) {
        if ( !key_exists($name, $this->_fields) ) {
            return NULL;
        }
        
        return $this->_fields[$name];
    }
    
    /**
     * Devuelve la lista de campos cargados
     * @return array La lista de campos
     */
    public function fields() {
        return array_keys($this->_fields);
    }
}
