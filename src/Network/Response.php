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

namespace PowerOn\Network;

use PowerOn\Exceptions\LogicException;

/**
 * Response
 * @author Lucas Sosa
 * @version 0.1
 */
class Response {
   /**
    * Configuración de la respusta
    * @var array
    */
    private $_config = [
        'buffer_size' => 8192
    ];
    
    /**
     * Cabezera por defecto a enviar
     * @var integer
     */
    private $_default_header = 200;
    /**
     * Status HTTP
     * @var array
     */
    private $_statuses = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out'
    ];
    /**
    * Crea una nueva respuesta
    * @param array $config La configuración: 
    * <ul>
    *   <li>'buffer_size' - The number of bytes each chunk of output should contain</li>
    * </ul>
    */
    public function __construct(array $config = []) {
        $this->_config += $config;
    }
    
   /**
    * Devuelve el estado http formateado
    * @param integer $code El codigo del estado http.
    * @return string
    */
    public function getStatus($code) {
        return key_exists($code, $this->_statuses) ?  "HTTP/1.1 {$code} {$this->_statuses[$code]}" : "HTTP/1.1 200 OK";
    }
        
    /**
     * Redirecciona a otra url inmediatamente
     * @param string $url La URL destino
     * @param int $status [Opcional] El codigo de estado http, por defecto es 302
     */
    public function redirect($url, $status = 302) {
        if ( !headers_sent() ) {
            header(sprintf('Location: %s', $url), TRUE, $status);
        }
    }
    
    /**
     * Establece una cabezera a enviar
     * @param type $code
     */
    public function setHeader($code) {
        if ( !headers_sent() ) {
            header( $this->getStatus($code) );
        }
    }
    
    public function defaultHeader($code) {
        $this->_default_header = $code;
    }
    
   /**
    * Renderiza la respuesta completa
    * @param mix $response_sent La respuesta puede ser un string con el body o un array 
    *   ['body' => BODY_CONTENT, 'headers' => [HEADER_TO_SEND], 'status' => STATUS_CODE ]
    * @param integer $status [Opcional] El codigo de estado HTTP, por defecto es 200
    */
    public function render($response_sent, $status = NULL) {
        $response = $this->_parse($response_sent);

        if (is_null($status)) {
            $status = $this->getStatus($this->_default_header);
        } elseif ( !is_null($status) ) {
            $status = $this->getStatus($status);
        } elseif ( !is_null($response['status']) ) {
            $status = $this->getStatus($response['status']);
        } else {
            $status = $this->getStatus(500);
        }
        
        if ( !headers_sent() ) {
            if (!strpos(PHP_SAPI, 'cgi')) {
                header($status);
            }
            foreach ( $response['headers'] as $header ) {
                header($header, false);
            }
        }
        $length = strlen($response['body']);
        for ( $i = 0; $i < $length; $i += $this->_config['buffer_size'] ) {
            echo substr($response['body'], $i, $this->_config['buffer_size']);
        }
    }
    
    /**
    * Da formato a una respuesta.
    * 
    * @param mix $response La respuesta a formatear, puede ser un array con los valores body, headers y status o un string con el body
    * @throws LogicException
    * @return array
    */
    private function _parse($response) {
        $defaults = [
            'body'    => '',
            'headers' => ['Content-Type: text/html; charset=utf-8'],
            'status'  => 200
        ];
        if ( is_array($response) ) {
            $response += $defaults;
        } elseif ( is_string($response) ) {
            $defaults['body'] = $response;
            $response = $defaults;
        } else {
            throw new LogicException('La respuesta no puede ser nula.', ['response' => $response]);
        }
        
        return $response;
    }
}
