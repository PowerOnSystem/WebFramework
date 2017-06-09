<?php
/*
 * Copyright (C) 2016 - 2020 PowerOn Sistamas
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
use PowerOn\Exceptions\DevException;
use PowerOn\Utility\Sanitize;

/**
 * Formularios de PowerOn
 * @author Lucas Sosa
 */

class Form {
    /** 
     * El nombre del formuario
     * @var string $name 
     */  
    public $name;
    /**
     * CSRF key preventor
     * @var string 
     */
    private $_token;
    /**
     * Los valores cargados
     * @var array
     */
    private $_values = [];
    /**
     * Esquema del formulario
     * @var Schema
     */
    private $_schema;
    /**
     * Validador
     * @var Validator
     */
    private $_validator;
    /**
     * Session Para controlar el token
     * @var \PowerOn\Network\Session 
     */
    private static $_session;
    /**
     * Inicializa el formulario
     * @throws DevException
     */
    public function initialize() {
        $reflection = new \ReflectionClass($this);
        $this->name = $reflection->getShortName();
        $this->_token = self::$_session->write('poweron_token', uniqid('tkn'));
        
        $this->_schema = $this->_buildSchema( new Schema() );
        if ( !$this->_schema instanceof Schema ) { 
            throw new \RuntimeException(sprintf('El formulario (%s) debe retornar un objeto de tipo Schema', $this->name),
                    ['return' => $this->_schema]);
        }
        $this->_validator = $this->_buildValidator( new Validator );
        if ( $this->_validator && !$this->_validator instanceof Validator) { 
            throw new \RuntimeException(sprintf('El validador del formulario (%s) debe retornar un objeto de tipo Validator', $this->name),
                    ['return' => $this->_validator]);
        }
    }
    
    public static function registerServices(\PowerOn\Network\Request $request) {
        self::$_session = $request->session();
    }
    
    /**
     * Ejecuta el formulario y realiza las validaciones correspondientes
     * @param array $data Datos recibidos del formulario por request
     * @return boolean Devuelve TRUE en caso exitoso o FALSE si ocurre algún error
     * @throws DevException
     */
    public function execute( array $data ) {
        $arguments = func_get_args();
        $this->_schema = $this->_buildSchema( new Schema() );
        $this->setValues( $data );
        $this->_validator = $this->_buildValidator( new Validator );
        
        if ( self::$_session->consume('poweron_token') != $data['poweron_token'] ) {
            $this->_validator->tokenError();
        } else if ( $this->_validator->validate( $this->_values ) ) {
            $arguments[0] = $this->_values;
            try {
                $r = call_user_func_array(array($this, '_execute'), $arguments);
                if ($r === NULL) {
                    throw new DevException('El formulario no retorn&oacute; ning&uacute;n valor.');
                }
                return $r;
            } catch (FormException $e) {
                $this->_validator->errors[$e->field][$e->field_id][] = $e->getMessage();
            }
        }
        
        return FALSE;
    }
    
    /**
     * Método a ejecutar en caso de que se complete el formulario
     * @param array $values Valores recogidos por el formulario
     * @return boolean Devuelve TRUE si no hay errores, o FALSE caso contrario
     */
    protected function _execute(array $values) {
        return $values;
    }
    
    /**
     * Establece el esquema del formulario
     * @param \PowerOn\Form\Schema $schema
     * @return \PowerOn\Form\Schema
     */
    protected function _buildSchema(Schema $schema) {
        return $schema;
    }
    
    /**
     * Establece las reglas de validación del formulario
     * @param \PowerOn\Form\Validator $validator
     * @return \PowerOn\Form\Validator
     */
    protected function _buildValidator(Validator $validator) {
        return $validator;
    }
    
    /**
     * Devuelve un valor recogido por el formulario
     * @param string $name
     * @return mix El valor recibido
     */
    protected function getValue( $name ) {
        return key_exists($name, $this->_values) ? $this->_values[$name] : NULL;
    }
    
    /**
     * Devuelve el Token del formulario
     * @return string
     */
    public function getToken() {
        return $this->_token;
    }
    
    /**
     * Devuelve el esquema cargado
     * @return Schema
     */
    public function getSchema() {
        return $this->_schema;
    }
    
    /**
     * Recoge los valores del formulario
     * @param array $data Datos recibidos del formulario
     */
    private function setValues( $data ) {
        $fields = $this->_schema->fields(); 
        foreach ( $fields as $name) {
            $field = $this->_schema->field($name);
            $this->_values[$name] = key_exists($name, $data) ? Sanitize::process( $data[$name], $field['sanitize'] ) : NULL;
        }
    }
}
