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
use Moment\Moment;
use PowerOn\Exceptions\DevException;
use PowerOn\Utility\Inflector;
use PowerOn\Utility\Str;

/**
 * Validador de campos de un formulario
 * @author Lucas Sosa
 * @version 0.1
 */
class Validator {
    /**
     * Reglas
     * @var Rule
     */
    private $_rules = [];
    /**
     * Error del validador
     * @var array 
     */
    private $_errors = [];
    /**
     * Advertencia del validador
     * @var string 
     */
    private $_warnings = [];

    /**
     * Reglas de validacion de campo strings
     */
    const STRING_RULES = ['alpha', 'numbers', 'spaces', 'low_strips', 'mid_strips', 'dots', 'commas', 'punctuation', 'quotes', 'symbols'];

        
    public function validStringAlpha($value) {
        return preg_match('/[a-zá-ú]/i', $value);
    }
    
    public function validStringNumbers($value) {
        return preg_match('/[0-9]/', $value);
    }
    
    public function validStringSpaces($value) {
        return preg_match('/ /', $value);
    }
    
    public function validStringLowstrips($value) {
        return preg_match('/_/', $value);
    }
    
    public function validStringMidstrips($value) {
        return preg_match('/-/', $value);
    }
    
    public function validStringDots($value) {
        return preg_match('/\./', $value);
    }
    
    public function validStringCommas($value) {
        return preg_match('/\,/i', $value);
    }
    
    public function validStringPunctuation($value) {
        return preg_match('/\¿|\?|\¡|\!/', $value);
    }
    
    public function validStringQuotes($value) {
        return preg_match('/\'|\"/i', $value);
    }
    
    public function validStringSymbols($value) {
        return preg_match('/[^\w|[a-zá-úÁ-Ú | |\ |\&|\*|\(|\)|\:|\+|\.|\,|\]|\[|\-|\;|\/|\?|\!|\¿|\¡|\'|\"|\#|\%|\$|@]/i', $value);
    }
    
    public function validStringMode($is_allow, $value, $param) {
        $errors = array();
        foreach (self::STRING_RULES as $p) {
            $function = 'validString' . Inflector::classify($p);
            $is_match = $this->$function($value);
            $is_requested = in_array($p, $param);
            $result = $is_requested ? ($is_allow ? TRUE : !$is_match) : ($is_allow ? !$is_match : TRUE);
            if ( !$result ) {
                switch ($p) {
                    case 'symbols'      : $r = 's&iacute;mbolos como (\\ [ ^)'; break;
                    case 'quotes'       : $r = 'comillas'; break;
                    case 'punctuation'  : $r = 's&iacute;mbolos de pregunta y admiraci&oacute;n'; break;
                    case 'commas'       : $r = 'comas'; break;
                    case 'dots'         : $r = 'puntos'; break;
                    case 'mid_strips'   : $r = 'gui&oacute;nes medios'; break;
                    case 'low_strips'   : $r = 'gui&oacute;nes bajos'; break;
                    case 'spaces'       : $r = 'espacios en blanco'; break;
                    case 'numbers'      : $r = 'n&uacute;meros'; break;
                    case 'alpha'        : $r = 'letras'; break;
                    default            : throw new DevException(sprintf('No se reconoce la regla de validaci&oacute;n (%s) en strings', $p));
                }
                $errors[] = $r;
            }
        }
        if ($errors) {
            throw new \Exception('Error, el campo no admite ' . implode(', ', $errors));
        }
    }
    
    public function validStringAllow($value, $param) {
        $this->validStringMode(TRUE, $value, $param);
    }
    
    public function validStringDeny($value, $param) {
        $this->validStringMode(FALSE, $value, $param);
    }
    
    public function validRequired($value) {
        if ( !$value && $value !== '0' ) {
            throw new \Exception('Este campo es requerido.');
        }
    }
    
    public function validJson($value) {
        if ( !\PowerOn\Core\PowerOnIsJson($value) ) {
            throw new \Exception('Los datos del campo no se recibieron de forma correcta.');
        }
    }
    
    public function validCustom($value, $param) {
        if ( !$param($value) ) {
            throw new \Exception($this->_rules->message);
        }
    }
    
    public function validUnique($value, $param) {
        if ( in_array($value, $param) ) {
            throw new \Exception('El valor ingresado debe ser &uacute;nico.');
        }
    }
    
    public function validMinSize($value, $param) {
        if ( $value && $value->size < $param ) {
            throw new \Exception(sprintf('El tama&ntilde;o del archivo no debe ser menor a (%s)', Str::bytestostr($param)));
        }
    }
    
    public function validMaxSize($value, $param) {
        if ( $value && $value->size > $param ) {
            throw new \Exception(sprintf('El tama&ntilde;o del archivo no debe superar los (%s), el archivo pesa (%s)'
                    ,Str::bytestostr($param), Str::bytestostr($value->size)));
        }
    }
    
    public function validUpload($value) {
        if ( $value && $value->error ) {
            $reason = 'No hay informaci&oacute;n del error';
            switch ($value->error) {
                case UPLOAD_ERR_INI_SIZE    : $reason = 'El peso del archivo supera el limite del servidor (100MB).'; break;
                case UPLOAD_ERR_FORM_SIZE   : $reason = 'El peso del archivo supera el limite impuesto por el formulario.'; break;
                case UPLOAD_ERR_PARTIAL     : $reason = 'El archivo fue parcialmente subido, intente nuevamente.'; break;
                case UPLOAD_ERR_NO_FILE     : $reason = 'No se subi&oacute; ning&uacute; fichero.'; break;
                case UPLOAD_ERR_NO_TMP_DIR  : $reason = 'Falta la carpeta temporal.'; break;
                case UPLOAD_ERR_CANT_WRITE  : $reason = 'No se pudo escribir el fichero en el disco.'; break;
                case UPLOAD_ERR_EXTENSION   : $reason = 'El archivo es potencialmente peligroso para el sistema.'; break;
            }
            throw new \Exception('No se pudo subir el archivo : ' . $reason);
        }
    }
    
    public function validExtension($value, $param) {
        if ( $value && !in_array($value->extension, explode(',', $param)) ) {
            throw new \Exception('No se admite la extensi&oacute;n '
                    . '(' . $value->extension . '), solo se admiten (' . $param . ').');
        }
    }
    
    public function validRequiredEither($value, $param) {
        if ( !$value && $param ) {
            throw new \Exception('El campo es requerido.');
        }
    }
    
    public function validEmail($value) {
        if ( $value && !preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', $value) ) {
            throw new \Exception('Debe ser un E-mail v&aacute;lido.');
        }
    }
    
    public function validUrl($value) {
        if ( $value && !preg_match('/^([a-z0-9\.-]+)\.([a-z\.]{2,6})([\/\w\?=.-]*)*\/?$/i', $value) ) {
            throw new \Exception('Debe ser una URL v&aacute;lida, asegurese de no utilizar (http://) al principio');
        }
    }

    public function validOptions($value, $param) {
        if ( $value && !in_array($value, $param) && !key_exists($value, $param) ) {
            throw new \Exception('La opci&oacute;n (' . $value . ') no esta permitida.');
        }
    }
    
    public function validCompare($value, $param) {
        if ( $value != $param ) {
            throw new \Exception('Los campos deben coincidir');
        }
    }
    
    public function validMinLength($value, $param) {
        if ( $value && strlen($value) < $param ) {
            throw new \Exception('Debe ten&eacute;r un m&iacute;nimo de (' . $param . ') caracteres.');
        }
    }
    
    public function validMaxLength($value, $param) {
        if ( $value && strlen($value) > $param ) {
            throw new \Exception('No debe superar los (' . $param . ') caracteres.');
        }
    }
    
    public function validExactLength($value, $param) {
        if ( $value && strlen($value) != $param ) {
            throw new \Exception('Debe tener exactamente (' . $param . ') caracteres.');
        }
    }
    
    public function validMaxVal($value, $param) {
        if ( $value > $param) {
             throw new \Exception('El n&uacute;mero no debe superar el valor (' . $param . ').');
        }
    }
    
    public function validMinVal($value, $param) {
        if ( $value < $param ) {
             throw new \Exception('El n&uacute;mero debe ser mayor a (' . $param . ').');
        }
    }
    
    public function validMaxDate($value, $param) {
        $this->validDate($value);
        $date = new Moment($value);
        if ( $value && $date->isAfter($param) ) {
            $date_param = new Moment($param);
            throw new \Exception(sprintf('La fecha debe ser anterior a (%s).', $date_param->format(Config::get('Date.date_time'))));
        }
    }
    
    public function validMinDate($value, $param) {
        $this->validDate($value);
        $date = new Moment($value);
        if ( $value && $date->isBefore($param) ) {
            $date_param = new Moment($param);
            throw new \Exception(sprintf('La fecha debe ser posterior a (%s).', $date_param->format(Config::get('Date.date_time'))));
        }
    }
    
    public function validDate($value) {
        $date = new Moment($value);
        if ( $value && !$date->format() ) {
            throw new \Exception('Debe ser una fecha v&aacute;lida');
        }
    }
    
    public function validDateTime($value) {
        $date = new Moment($value);
        if ( !$date->format() ) {
            throw new \Exception('Debe ser una fecha y hora v&aacute;lida');
        }
    }
    
    public function validTime($value) {
        $date = new Moment($value);
        if ( $value && !$date->format() ) {
            throw new \Exception('Debe ser una hora v&aacute;lida');
        }
    }

    /**
     * Valida las reglas actuales
     * @return boolean
     */
    public function validate(array $values) {
        $return = TRUE;
        foreach ($this->_rules as $field => $rules) {
            foreach ($rules as $rule) {
                $field_id = 0;
                try {
                    if ( key_exists($field, $values) ) {
                        $function = 'valid' . Inflector::classify($rule->name);
                        if ( is_array($values[$field]) && !in_array($rule->name, ['options', 'unique']) ) {
                            foreach ($values[$field] as $field_id => $value) {
                                $param = is_array($rule->param) && !in_array($rule->name, ['string_allow', 'string_deny']) ?
                                        $rule->param[$field_id] : $rule->param;
                                $this->{$function} ( $value, $param );  
                            }
                        } else {
                            $this->{$function} ( $values[$field], $rule->param );  
                        }
                    }
                } catch (\Exception $e) {
                    switch ($rule->level) {
                        case Rule::ERROR    :
                            $this->_errors[$field] = $rule->message ? $rule->message : $e->getMessage();
                            $return = FALSE;
                            break;
                        case Rule::WARNING  : 
                            $this->_warnings[$field] = $rule->message ? $rule->message : $e->getMessage();
                            break;
                    }
                }
            }
        }
        
        return $return;
    }
    
    
    /**
     * Agrega una regla de validación
     * @param string $field El nombre del campo a agregar la regla
     * @param string|array $rule El nombre de la regla: ['required', 'options', 'compare', 'min_length', 'max_length',
            'exact_length', 'min_val', 'max_val', 'min_date', 'max_date', 'date', 
            'date_time', 'time', 'url', 'email', 'required_either', 'extension', 'json',
            'max_size', 'min_size', 'unique', 'string_allow', 'string_deny', 'custom']
     * @param mix $param Los parámetros de la regla
     * @param string $level [Opcional] El nivel de alerta, Si es ERROR no procede, si es WARNING solo genera una advertencia
     * @param string $message [Opcional] Texto que reemplazará el mensaje de error
     * @param bolean $is_dinamic Especifica si se trata de un parámetro dinámico recolectado en base a la informacíon recibida de un formulario
     * @return Validator
     */
    public function add($field, $rule, $param = NULL, $level = NULL, $message = NULL, $is_dinamic = FALSE) {
        if ( is_array($rule) ) {
            foreach ($rule as $data) {
                $r = [
                    'rule' => NULL,
                    'param' => $param,
                    'level' => $level,
                    'message' => $message,
                    'is_dinamic' => $is_dinamic
                ] + $data
                        ;
                $this->_rules[$field][$r['rule'] == NULL ? $data[0] : $r['rule']] = new Rule($r['rule'] == NULL ? $data[0] : $r['rule'],
                        $r['param'] == NULL && key_exists(1, $data) ? $data[1] : $r['param'],
                        $r['level'] == NULL && key_exists(2, $data) ? $data[2] : $r['level'],
                        $r['message'] == NULL && key_exists(3, $data) ? $data[3] : $r['message'],
                        $r['is_dinamic'] == NULL && key_exists(4, $data) ? $data[4] : $r['is_dinamic']);
            }
        } else {
            $this->_rules[$field][$rule] = new Rule($rule, $param, $level, $message, $is_dinamic);
        }

        return $this;
    }
    
    public function getErrors() { 
        return $this->_errors;
    }
}
