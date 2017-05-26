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
 * Container
 * @author Lucas Sosa
 * @version 0.1
 */
class Container {
    /**
     * Instancias cargadas
     * @var array
     */
    private $_instances = [];
    
    /**
     * Dependencias preconfiguradas
     * @var array
     */
    private $_dependencies = [];
    
    /**
     * Crea las dependencias
     * @param string $file
     * @throws DevException
     */
    public function buildDependencies($file) {
        if ( !is_file($file) ) {
            throw new DevException('Las dependencias enviadas deben ser un archivo que resulte en un array.', ['file' => $file]);
        }
        
        $this->_dependencies = include $file;
    }
    
    public function pushDependency($name, $instance) {
        if ( !is_object($instance) ) {
            throw new DevException('La dependencia a agregar debe ser una clase', ['instance' => $instance]);
        }
        $this->_instances[$name] = $instance;
    }
    
    /**
     * Obtiene una clase cargada en dependencias
     * @param string $class
     * @return object
     * @throws DevException
     */
    public function get($class) {
        if ( !key_exists($class, $this->_dependencies) ) {
            throw new DevException(sprintf('La dependencia (%s) no existe.', $class));
        }
        
        if ( in_array($class, $this->_dependencies[$class]) ) {
            throw new DevException(sprintf('La clase (%s) no puede depender de si misma.', $class));
        }
        
        if ( !key_exists($class, $this->_instances) ) {
            $instance = $this->_make( $class );
            if ( !key_exists('newObject', $this->_dependencies[$class]) ) {
                $this->_instances[$class] = $instance;
            } else {
                return $instance;
            }
        }
        
        return $this->_instances[$class];
    }
    
    /**
     * Llama a un metodo de una clase e injecta las dependencias
     * @param object $instance La instancia de la clase
     * @param string $method El método de la clase a llamar
     * @param array $additional_params Parámetros adicionales a pasar al metodo
     * @throws DevException
     */
    public function method($instance, $method, $additional_params = []) {
        
        $r = new \ReflectionClass($instance);
        $name = $r->getName() . '::' . $method;
        
        if ( !method_exists($instance, $method) ) {
            throw new DevException(sprintf('El m&eacute;todo (%s) no existe en la clase (%s)', 
                    $method, $r->getName()), ['instance' => $instance]);
        }
        
        if ( !key_exists($name, $this->_dependencies) ) {
            $name = $r->getParentClass()->name . '::' . $method;
        }
        
        if ( !key_exists($name, $this->_dependencies) ) {
            throw new DevException(sprintf('El m&eacute;todo (%s) de la clase (%s) no tiene dependencias declaradas',
                    $method, $r->getName()), ['dependencies' => $this->_dependencies]);
        }
        
        $params = $this->_params($this->_dependencies[$name]) + $additional_params;
        
        call_user_func_array([$instance, $method], $params);
    }
    
    /**
     * Crea la clase e invoca las dependecias
     * @param string $class
     * @return object
     */
    private function _make($class) {
        $params = $this->_params($this->_dependencies[$class]);
        if ( count($params) == 0) {
            return new $class;
        } else {
            $r = new \ReflectionClass($class);
            return $r->newInstanceArgs($params);
        }
    }
    
    /**
     * Devuelve las dependencias solicitadas
     * @param array $dependencies Las dependencias del objeto
     * @return array Devuelve un array con los objetos cargados y sus respectivas dependencias
     */
    private function _params($dependencies) {
        $params = [];
        foreach ($dependencies as $d) {
            if ( key_exists($d, $this->_instances) ) {
                $params[] = $this->_instances[$d];
            } else if ( key_exists($d, $this->_dependencies) ) {
                $params[] = $this->get($d);
            } else {
                $params[] = new $d();
            }
        }
        
        return $params;
    }
}
