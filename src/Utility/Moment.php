<?php
/*
 * Copyright (C) Makuc Julian & Makuc Diego S.H.
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
 * Clase Moment
 * Controla todas las fechas
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016 - 2020, PowerOn Sistemas
 */
class Moment {

    protected $timestamp;
    public $requested;
    
    protected $days_name = array(1 => "Lunes", 2 => "Martes", 3 => "Miercoles", 4 => "Jueves", 5 => "Viernes", 6 => "S&aacute;bado", 7 => "Domingo");
    protected $months_name = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

    public $day, $month, $year, $dayweek, $week, $hour, $min, $second, $ampm;
    /**
     * Constructor de la Clase
     * @param mix $time Moment|String|Timestamp
     */
    public function __construct($time = NULL) {
        $this->requested = $time;
        $this->timestamp = $this->check($time);
        $this->configure();
    }
    
    private function configure() {
        $this->day = date('j', $this->timestamp);
        $this->year = date('Y', $this->timestamp);
        $this->dayweek = date('N', $this->timestamp);
        $this->week = date('W', $this->timestamp);
        $this->month = date('n', $this->timestamp);
        $this->hour = date('g', $this->timestamp);
        $this->min = date('i', $this->timestamp);
        $this->second = date('s', $this->timestamp);
        $this->ampm = date('a', $this->timestamp);
    }
    
    /**
     * Verifica si la fecha entregada es valida
     * @return boolean
     */
    public function isValid() {
        return !$this->requested ? FALSE : 
                ( (is_numeric($this->requested) && date('Y-m-d', $this->requested) === $this->format('Y-m-d'))
                || (is_string($this->requested) && strtotime($this->requested)) 
                || $this->requested instanceof Moment ? TRUE : FALSE );
    }
    
    /**
     * Devuelve el unix timestamp
     * @return int
     */
    public function unix() {
        return $this->timestamp;
    }
    
    /**
     * Devuelve la fecha y hora
     * @return string
     */
    public function datetime() {
        return date(CNC_DB_DATE_TIME_FORMAT, $this->timestamp);
    }
    
    /**
     * Devuelve la fecha sola
     * @return string
     */
    public function date() {
        return date(CNC_DB_DATE_FORMAT, $this->timestamp);
    }
    
    /**
     * Devuelve la hora especificad
     * @return string
     */
    public function time() {
        return date(CNC_DB_TIME_FORMAT, $this->timestamp);
    }
    /**
     * Obtiene la fecha en un formato específico
     * @param string $format
     * @return string
     */
    public function format($format = CNC_DB_DATE_TIME_FORMAT) {
        return date($format, $this->timestamp);
    }
    
    /**
     * Devuelve la distancia entre dos fechas
     * @param mix $time El tiempo de la fecha final
     * @param boolean $prefix Especfica si se utiliza el prefijo 
     * @return string
     */
    public function to($time = NULL, $prefix = TRUE) {
        $to = $this->check($time);
        if ($this->timestamp < $to) {
            $diff = $to - $this->timestamp;
            $pfx = 'Hace';
        } else if ($this->timestamp > $to) {
            $diff = $this->timestamp - $to;
            $pfx = 'Dentro de';
        } else {
            return 'Ahora';
        }
        
        if ($diff < 60) {
            $return =  $diff . ' segundos';
        } elseif ($diff < 120) {
            $return =  "un minuto";
        } elseif ($diff < 2700) {
            $return =  floor($diff / 60) . ' minutos';
        } elseif ($diff < 5400) {
            $return =  "una hora";
        } elseif ($diff < 86400) {
            $return = floor($diff / 3600) . ' horas';
        } elseif ($diff < 172000) {
            $return =  "un d&iacute;a";
        } elseif ($diff < 2592000) {
            $return = floor($diff / 86400) . ' dias';
        } elseif ($diff < 31104000) {
            $amount = floor($diff / 86400 / 30);
            $return =  $amount <= 1 ? 'un m&eacute;s' : $amount . ' meses';
        } else {
            $amount = floor($diff / 86400 / 365);
            $return =  $amount <= 1 ? 'un a&ntilde;o' : $amount . " a&ntilde;os";
        }
        return ($prefix ? $pfx . ' ' : '') . $return;
    }
    
    public function toHumanize($time = NULL) {
        $to = $this->check($time);
        $same_day = $this->isSame($to, array('day', 'month', 'year'));
        $r = 'Desde el ' . $this->format($same_day ? CNC_DATE_FORMAT : CNC_DATE_TIME_FORMAT) . ($same_day ? ' de ' . $this->format(CNC_TIME_FORMAT) : '');
        $r .= $same_day ? ' a ' . date(CNC_TIME_FORMAT, $to) . 'hs' : 'Hasta el ' . date(CNC_DATE_TIME_FORMAT);
        
        return $r;
    }
    
    /**
     * Computa si la fecha actual es anterior a la dada
     * @param mix $time La fecha a calcular
     * @param string $flag [Opcional] Si se requiere calcular determinada porcion de la fecha (year, month, day, hour, minute, second)
     * @return boolean
     */
    public function isBefore($time, $flag = NULL) {
        $bf = $this->check($time);

        if ($flag == 'year') {
            return date('Y', $this->timestamp) > date('Y', $bf); 
        } else if ($flag == 'month') {
            return date('n', $this->timestamp) > date('n', $bf); 
        } else if ($flag == 'day') {
            return date('j', $this->timestamp) > date('j', $bf); 
        } else if ($flag == 'hour') {
            return date('G', $this->timestamp) > date('G', $bf); 
        } else if ($flag == 'minute') {
            return date('i', $this->timestamp) > date('i', $bf); 
        } else if ($flag == 'second') {
            return date('s', $this->timestamp) > date('s', $bf); 
        } else if ($flag == 'week') {
            return date('W', $this->timestamp) > date('W', $bf); 
        }

        return $this->timestamp < $bf;
    }
    
    /**
     * Computa si la fecha actual es posterior a la dada
     * @param mix $time La fecha a calcular
     * @param string $flag [Opcional] Si se requiere calcular determinada porcion de la fecha (year, month, day, hour, minute, second)
     * @return boolean
     */
    public function isAfter($time, $flag = NULL) {
        $aft = $this->check($time);
        if ($flag == 'year') {
            return date('Y', $this->timestamp) < date('Y', $aft); 
        } else if ($flag == 'month') {
            return date('n', $this->timestamp) < date('n', $aft); 
        } else if ($flag == 'day') {
            return date('j', $this->timestamp) < date('j', $aft); 
        } else if ($flag == 'hour') {
            return date('G', $this->timestamp) < date('G', $aft); 
        } else if ($flag == 'minutes') {
            return date('i', $this->timestamp) < date('i', $aft); 
        } else if ($flag == 'seconds') {
            return date('s', $this->timestamp) < date('s', $aft); 
        } else if ($flag == 'week') {
            return date('W', $this->timestamp) < date('W', $aft); 
        }
        
        return $this->timestamp > $aft;
    }
    
    /**
     * Computa si la fecha actual es igual a la dada
     * @param mix $time La fecha a calcular
     * @param mix $flag [Opcional] Si se requiere calcular determinada porcion de la fecha (year, month, day, hour, minute, second)
     * @return boolean
     */
    public function isSame($time, $flag = NULL) {
        $sm = $this->check($time);
        if ( is_array($flag) && $flag ) {
            foreach ($flag as $f) {
                if ( !$this->isSame($sm, $f) ) {
                    return FALSE;
                }
            }
            return TRUE;
        }
        
        if ($flag == 'year') {
            return date('Y', $this->timestamp) == date('Y', $sm); 
        } else if ($flag == 'month') {
            return date('n', $this->timestamp) == date('n', $sm); 
        } else if ($flag == 'day') {
            return date('j', $this->timestamp) == date('j', $sm); 
        } else if ($flag == 'hour') {
            return date('G', $this->timestamp) == date('G', $sm); 
        } else if ($flag == 'minutes') {
            return date('i', $this->timestamp) == date('i', $sm); 
        } else if ($flag == 'seconds') {
            return date('s', $this->timestamp) == date('s', $sm); 
        } else if ($flag == 'week') {
            return date('W', $this->timestamp) == date('W', $sm); 
        }
        
        return $this->timestamp == $sm;
    }
    
    /**
     * Computa si la fecha actual es igual o anterior a la dada
     * @param mix $time La fecha a calcular
     * @param string $flag [Opcional] Si se requiere calcular determinada porcion de la fecha (year, month, day, hour, minute, second)
     * @return boolean
     */
    public function isSameOrBefore($time, $flag = NULL) {
        $smbf = $this->check($time);
        if ($flag == 'year') {
            return date('Y', $this->timestamp) >= date('Y', $smbf); 
        } else if ($flag == 'month') {
            return date('n', $this->timestamp) >= date('n', $smbf); 
        } else if ($flag == 'day') {
            return date('j', $this->timestamp) >= date('j', $smbf); 
        } else if ($flag == 'hour') {
            return date('G', $this->timestamp) >= date('G', $smbf); 
        } else if ($flag == 'minutes') {
            return date('i', $this->timestamp) >= date('i', $smbf); 
        } else if ($flag == 'seconds') {
            return date('s', $this->timestamp) >= date('s', $smbf); 
        } else if ($flag == 'week') {
            return date('W', $this->timestamp) >= date('W', $smbf); 
        }
        
        return $this->timestamp <= $smbf;
    }
    
    /**
     * Computa si la fecha actual es igual o posterior a la dada
     * @param mix $time La fecha a calcular
     * @param string $flag [Opcional] Si se requiere calcular determinada porcion de la fecha (year, month, day, hour, minute, second)
     * @return boolean
     */
    public function isSameOrAfter($time, $flag = NULL) {
        $smat = $this->check($time);
        if ($flag == 'year') {
            return date('Y', $this->timestamp) <= date('Y', $smat); 
        } else if ($flag == 'month') {
            return date('n', $this->timestamp) <= date('n', $smat); 
        } else if ($flag == 'day') {
            return date('j', $this->timestamp) <= date('j', $smat); 
        } else if ($flag == 'hour') {
            return date('G', $this->timestamp) <= date('G', $smat); 
        } else if ($flag == 'minutes') {
            return date('i', $this->timestamp) <= date('i', $smat); 
        } else if ($flag == 'seconds') {
            return date('s', $this->timestamp) <= date('s', $smat); 
        } else if ($flag == 'week') {
            return date('W', $this->timestamp) <= date('W', $smat); 
        }
        
        return $this->timestamp >= $smat;
    }
    
    /**
     * Computa si la fecha actual esta dentro del rango especificado
     * @param mix $start La fecha inicial a calcular
     * @param mix $end La fecha final a calcular
     * @param string $flag [Opcional] Si se requiere calcular determinada porcion de la fecha (year, month, day, hour, minute, second)
     * @return boolean
     */
    public function isBetween($start, $end, $flag = NULL) {
        $bws = $this->check($start);
        $bwe = $this->check($end);
        if ($flag == 'year') {
            return date('Y', $this->timestamp) >= date('Y', $bws) && date('Y', $this->timestamp) <= date('Y', $bwe); 
        } else if ($flag == 'month') {
            return date('n', $this->timestamp) >= date('n', $bws) && date('n', $this->timestamp) <= date('n', $bwe); 
        } else if ($flag == 'day') {
            return date('j', $this->timestamp) >= date('j', $bws) && date('j', $this->timestamp) <= date('j', $bwe); 
        } else if ($flag == 'hour') {
            return date('G', $this->timestamp) >= date('G', $bws) && date('G', $this->timestamp) <= date('G', $bwe); 
        } else if ($flag == 'minutes') {
            return date('i', $this->timestamp) >= date('i', $bws) && date('i', $this->timestamp) <= date('i', $bwe); 
        } else if ($flag == 'seconds') {
            return date('s', $this->timestamp) >= date('s', $bws) && date('s', $this->timestamp) <= date('s', $bwe); 
        } else if ($flag == 'week') {
            return date('W', $this->timestamp) >= date('W', $bws) && date('W', $this->timestamp) <= date('W', $bwe); 
        }
        
        return $this->timestamp >= $bws && $this->timestamp <= $bwe;
    }
    
    /**
     * Computa si la fecha actual es hoy
     * @return boolean
     */
    public function isToday() {
        return date('Y-m-d', $this->timestamp) == date('Y-m-d');
    }
    
    /**
     * Computa si la fecha actual es mañana
     * @return boolean
     */
    public function isTomorrow() {
        return date('Y-m-d', $this->timestamp) == date('Y-m-d', strtotime(date('Y-m-d') . ' +1 day'));
    }
    
    /**
     * Computa si la fecha actual fue ayer
     * @return boolean
     */
    public function isYesterday() {
        return date('Y-m-d', $this->timestamp) == date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day'));
    }
    
    /**
     * Computa si la fecha es la semana que viene
     * @return boolean
     */
    public function isNextWeek() {
        return date('W') + 1 == $this->week;
    }
    
    /**
     * Computa si la fecha fue la semana pasada
     * @return boolean
     */
    public function isPreviousWeek() {
        return date('W') - 1 == $this->week;
    }
    
    /**
     * Computa si la fecha es la semana que viene
     * @return boolean
     */
    public function isNextMonth() {
        return date('m') + 1 == $this->month;
    }
    
    /**
     * Computa si la fecha fue la semana pasada
     * @return boolean
     */
    public function isPreviousMonth() {
        return date('m') - 1 == $this->month;
    }
    
    /**
     * Agrega una determinada cantidad a la fecha actual
     * @param type $ammount La cantidad a agregar
     * @param type $type El tipo de fecha  agregar (year, month, day, hour, minute, second)
     */
    public function add($ammount, $type) {
        if ( !in_array($type, array('year', 'month', 'day', 'hour', 'minute', 'second', 'week'))) {
            return FALSE;
        }
        $this->timestamp = strtotime($this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->hour . ':' . $this->min . ':' . $this->second . ' +' . $ammount . ' ' . $type);
        $this->configure();
        return $this;
    }
    
    /**
     * Agrega una determinada cantidad a la fecha actual
     * @param type $ammount La cantidad a agregar
     * @param type $type El tipo de fecha  agregar (year, month, day, hour, minute, second)
     */
    public function subtract($ammount, $type) {
        if ( !in_array($type, array('year', 'month', 'day', 'hour', 'minute', 'second', 'week'))) {
            return FALSE;
        }
        $this->timestamp = strtotime($this->year . '-' . $this->month . '-' . $this->day . ' ' . $this->hour . ':' . $this->min . ':' . $this->second . ' -' . $ammount . ' ' . $type);
        $this->configure();
        return $this;
    }
    
    /**
     * Muestra en un formato entendible la fecha completa actual
     * @return string
     */
    public function humanize() {
        return $this->humanizeDate() . ' ' . $this->humanizeHours();
    }
    
    /**
     * Muestra en un formato entendible la hora actual
     * @return string
     */
    public function humanizeHours() {
        $hour = $this->hour > 0 ? 'a las ' . $this->hour : 'a las 12:';
        $min = ':' . $this->min;
        $ampm = $this->ampm == 'am' ? ' de la ma&ntilde;ana' : ' de la tarde';
        return $hour . $min . $ampm;
    }
    
    /**
     *  Muestra en un formato entendible la fecha actual
     * @return type
     */
    public function humanizeDate() {
        $close_date = $this->isNextWeek() || $this->isPreviousWeek();
        $day = $this->isToday() ? 'Hoy' : ( $this->isNextWeek() ? 'Pr&oacute;ximo ' : '' ) . $this->days_name[$this->dayweek] . ($close_date ? '' : ' ' . $this->day) . ' ' . ($this->isPreviousWeek() ? ' pasado' : '');
        $month = $this->isToday() || $this->isSame(NULL, 'month') || $close_date ? '' : ' de ' . $this->months_name[$this->month - 1];
        $year = $this->isSame(NULL, 'year') ? '' : ' del ' . $this->year;
        
        return $day . $month . $year;
    }
    
    /**
     * Evalua la variable recibida para computar el formato
     * @param mix $time el tiempo  Moment|String|Timestamp
     * @return mix
     */
    private function check($time) {
        $data = $time === NULL || $time === '' ? time() : (
            is_numeric($time) ? $time : (
                $time instanceof Moment ? $time->unix() : (
                    is_string($time) ? strtotime($time) : FALSE
                )
            )
        );
        
        if ( !$data ) {
            throw new DevException('No se puede trabajar con el tiempo solicitado', array('time' => $time));
        }
        
        return $data;
    }
}