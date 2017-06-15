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
use PowerOn\Exceptions\DevException;
use PowerOn\Utility\Moment;
use PowerOn\Utility\Inflector;

/**
 * Description of Rules
 *
 * @author Lucas Sosa
 */
class Rule {
    /**
     * Nombre de la regla
     * @var string 
     */
    public $name;
    /**
     * Los parametros de la regla
     * @var mix 
     */
    public $param;
    /**
     * Nivel de 
     * @var string 
     */
    public $level;
    /**
     * Mensaje de error personalizado
     * @var string 
     */
    public $message;
    /**
     * Si es un parámetro dinámico
     * @var boolean
     */
    public $dinamic = FALSE;
    
    const ERROR = 0;
    const WARNING = 1;
    /**
     * Crea una nueva regla de validación
     * @param string $name La regla: ['required', 'options', 'compare', 'min_length', 'max_length',
            'exact_length', 'min_val', 'max_val', 'exact_val', 'date', 'date_time', 'time', 
            'url', 'email', 'required_either', 'extension', 'max_size', 'min_size', 'unique', 
            'string_allow', 'string_deny', 'custom', 'upload']
     * @param mix $param Los parametros de la regla
     * @param string $message [Opcional] Texto que reemplazará el mensaje de error
     * @param bolean $is_dinamic Especifica si se trata de un parámetro dinámico recolectado en base a la informacíon recibida de un formulario
     * @throws DevException
     */
    public function __construct( $name, $param, $level = NULL, $message = '', $is_dinamic = FALSE) {
        $available_rules = array('required', 'options', 'compare', 'min_length', 'max_length',
            'exact_length', 'min_val', 'max_val', 'min_date', 'max_date', 'min_date_field', 'max_date_field', 'date', 
            'date_time', 'time', 'url', 'email', 'required_either', 'extension', 'json',
            'max_size', 'min_size', 'unique', 'string_allow', 'string_deny', 'custom', 'upload');
        
        if ( !in_array($name, $available_rules) ) {
            throw new DevException('No se reconoce la regla de validaci&oacute;n (' . $name . ')', 
                    array('available_rules' => $available_rules));
        }
        $this->validateRule($name, $param);
        $this->name = $name;
        $this->param = $param;
        $this->message = $message;
        $this->dinamic = $is_dinamic;
        $this->level = $level === self::WARNING ? self::WARNING : self::ERROR;
    }

    public function validateRule($name, $param) {
        $function = 'validate' . Inflector::classify($name);
        $this->{$function}($name, $param);
    }
    
    public function validateJson($name, $param) {
        $this->validateRequired($name, $param);
    }
    
    public function validateRequired($name, $param) {
        if ( !is_bool($param) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ') '
                    . 'debe ser condicional', array('param' => $param));
        }
    }
    
    public function validateUpload($name, $param) {
        if ( !is_bool($param) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ') '
                    . 'debe ser condicional', array('param' => $param));
        }
    }
    
    public function validateOptions($name, $param) {
         if ( !$param ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ') '
                    . 'debe tener alg&uacute;n elemento', array('param' => $param));
        }
        if ( !is_array($param) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ')'
                    . ' debe ser un array', array('param' => $param));
        }
    }
    
    public function validateCompare($name, $param) {
        if ( !$param ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ')'
                    . ' no debe estar en blanco', array('param' => $param));
        }
    }
    
    public function validateMinLength($name, $param) {
        if ( !is_numeric($param) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ') '
                    . 'debe ser un valor num&eacute;rico', array('param' => $param));
        }
    }
    
    public function validateMaxLength($name, $param) {
        $this->validateMinLength($name, $param);
    }
    
    public function validateMaxVal($name, $param) {
        $this->validateMinLength($name, $param);
    }
    
    public function validateMinVal($name, $param) {
        $this->validateMinLength($name, $param);
    }
    
    public function validateExactLength($name, $param) {
        $this->validateMinLength($name, $param);
    }
    
    public function validateMinDate($name, $param) {
        if ( $param != NULL) {
            $params = is_array($param) ? $param : array($param);
            foreach ($params as $p) {
                $m = new Moment($p);
                if ( !$m->isValid() ) {
                    throw new DevException('El par&aacute;metro de la regla (' . $name . ')'
                            . ' debe ser una fecha v&aacute;lida', array('param' => $p));
                }
            }
        }
    }
    
    public function validateMaxDate($name, $param) {
        $this->validateMinDate($name, $param);
    }
    
    public function validateMinDateField($name, $param) {
        $this->validateCompare($name, $param);
    }
    
    public function validateMaxDateField($name, $param) {
        $this->validateCompare($name, $param);
    }
    
    public function validateDate($name, $param) {
        $this->validateRequired($name, $param);
    }
    
    public function validateDateTime($name, $param) {
        $this->validateRequired($name, $param);
    }
    
    public function validateTime($name, $param) {
        $this->validateRequired($name, $param);
    }
    
    public function validateUrl($name, $param) {
        $this->validateRequired($name, $param);
    }
    
    public function validateEmail($name, $param) {
        $this->validateRequired($name, $param);
    }
    
    public function validateRequiredEither($name, $param) {
        if ( !is_string($param) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ')'
                    . ' debe ser un string v&aacute;lido', array('param' => $param));
        }
    }
    
    public function validateExtension($name, $param) {
        $this->validateCompare($name, $param);
    }
    
    public function validateMinSize($name, $param) {
        $this->validateMinLength($name, $param);
    }
    
    public function validateMaxSize($name, $param) {
        $this->validateMinLength($name, $param);
    }
    
    public function validateUnique($name, $param) {
        //$this->validateOptions($name, $param);
    }
    
    public function validateCustom($name, $param) {
        if ( !is_object($param) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ')'
                    . ' debe ser una funci&oacute;n v&aacute;lida', array('param' => $param));
        }
    }
    
    public function validateStringDeny($name, $param) {
        $this->validateOptions($name, $param);
        $string_rules = explode(',', Validator::STRING_RULES);
        if ( array_diff($param, $string_rules) ) {
            throw new DevException('El par&aacute;metro de la regla (' . $name . ') '
                    . 'posee uno o m&aacute;s par&aacute;metros incorrectos', 
                    array(
                        'param' => $param, 'errors' => array_diff($param, $string_rules),
                        'availables' => $string_rules
                    )
                );
        }
    }
    
    public function validateStringAllow($name, $param) {
        $this->validateStringDeny($name, $param);
    }
}
