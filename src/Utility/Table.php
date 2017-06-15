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

namespace CNCService\Utility;
use CNCService\Core\CoreException;

/**
 * Table
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class Table {
    
    public $title;
    public $width;
    public $class;
    public $sort = FALSE;
    public $sort_by = NULL;
    public $sort_mode = NULL;
    public $advance_search = NULL;
    public $advance_search_fields = array();
    public $search_fields = array();
    public $search_values = array();
    public $search_mode;
    /**
     *
     * @var Pager
     */
    public $pager ;
    public $fast_search = NULL;
    
    public $body = array();
    public $rows = array();
    public $header = array();
    
    private $pointer = 0;

    /**
     * Nombre de la tabla automática
     * @var string
     */
    private $auto_table = NULL;
    
    const TABLE_WORKS = 'TABLE_WORKS';
    /**
     * Inicia una nueva tabla a mostrar
     * @param mix $table
     */
    public function __construct( $table ) {
        if ( is_string($table) ) {
            $this->autoTable( $table );
        } else if ( is_array($table) ) {
            $this->configureTable( $table );
        }
    }
    
    private function autoTable( $table_name ) {
        switch ($table_name) {
            case self::TABLE_WORKS  :
                $this->class = 'selectable';
                break;
            default                 : throw new CoreException('No se reconoce el nombre de la tabla (' . $table_name . ')');
        }
        
        $this->auto_table = $table_name;
    }
    
    /**
     * Configura la tabla actual
     * @param array $params
     */
    private function configureTable( array $params ) {
        $this->title = key_exists('title', $params) ? $params['title'] : NULL;
        $this->width = key_exists('width', $params) ? $params['width'] : '100%';
        $this->class = key_exists('class', $params) ? $params['class'] : '';
        $this->sort = key_exists('sort', $params) ? $params['sort'] : FALSE;
        $this->sort_by = key_exists('sort_by', $params) ? $params['sort_by'] : FALSE;
        $this->sort_mode = key_exists('sort_mode', $params) ? $params['sort_mode'] : FALSE;
        $this->pager = key_exists('pager', $params) ? $params['pager'] : array();
        $this->fast_search = key_exists('fast_search', $params) ? $params['fast_search'] : FALSE;
        $this->advance_search = key_exists('advance_search', $params) ? $params['advance_search'] : FALSE;
        $this->advance_search_fields = key_exists('advance_search_fields', $params) ? $params['advance_search_fields'] : array();
        $this->search_fields = key_exists('search_fields', $params) && $params['search_fields'] ? 
                explode(',', $params['search_fields']) : array();
        $this->search_values = key_exists('search_values', $params) && $params['search_values'] ? 
                explode(',', $params['search_values']) : array();
        $this->search_mode = key_exists('search_mode', $params) ? $params['search_mode'] : array();
    }
    
    /**
     * Crea un elemento en el encabezado
     * @param string $name El nombre del elemento
     * @param string $title [Opcional] El titulo a mostrar
     * @param array $params [Opcional] Los parámetros adicionales
     * @return \CNCService\Utility\Table
     */
    public function head($name, $title = '', array $params = array()) {
        $this->header[$name] = $params + array(
                'type' => NULL,
                'title' => $title,
                'width' => 'auto',
                'class' => ''
            );
        
        return $this;
    }
    /**
     * 
     * @param type $column
     * @param type $content
     * @param array $params
     * @return \CNCService\Utility\Table
     */
    public function add($column, $content, array $params = array()) {
        $this->body[$this->pointer][$column] = $params + array(
            'color' => NULL,
            'content' => $content,
            'element' => 'content'
        );
        return $this;
    }
    
    /**
     * El botón de acceso al registro de la tabla
     * @param array $url
     * @param array $params
     * @return \CNCService\Utility\Table
     */
    public function addJoin(array $url, array $params = array()) {
        $this->body[$this->pointer]['join'] = $params + array(
            'url' => $url,
            'color' => NULL,
            'element' => 'join'
        );
        
        return $this;
    }
    
    /**
     * Agrega un contacto
     * @param mix $column
     * @param \CNCService\Modules\Contacts\Model\Entities\Contact $contact
     * @return \CNCService\Utility\Table
     */
    public function addContact($column, \CNCService\Modules\Contacts\Model\Entities\Contact $contact) {
        $this->body[$this->pointer][$column] = array(
            'contact' => $contact,
            'color' => NULL,
            'element' => 'contact'
        );
        return $this;
    }
    
    /**
     * Agrega la dirección de un contacto
     * @param mix $column
     
     * @return \CNCService\Utility\Table
     */
    public function addContactAddress($column, $address, $locality, $state, $city, $country, $zip) {
        $this->body[$this->pointer][$column] = array(
            'address' => $address,
            'locality' => $locality,
            'state' => $state,
            'city' => $city,
            'country' => $country,
            'zip' => $zip,
            'color' => NULL,
            'element' => 'address'
        );
        return $this;
    }
    
    /**
     * Agrega el teléfono de un contacto
     * @param mix $column

     * @return \CNCService\Utility\Table
     */
    public function addContactPhones($column, $phones, $prefix = NULL) {
        $this->body[$this->pointer][$column] = array(
            'phones' => $phones,
            'prefix' => $prefix,
            'color' => NULL,
            'element' => 'phones'
        );
        return $this;
    }
    
    /**
     * Agrega una orden de trabajo
     * @param mix $column
     * @param integer $number
     * @param \CNCService\Work\Entities\Order $order
     * @return \CNCService\Utility\Table
     */
    public function addOrder($column, $number, \CNCService\Work\Entities\Order $order) {
        $this->body[$this->pointer][$column] = array(
            'order' => $order,
            'number' => $number,
            'color' => NULL,
            'element' => 'order'
        );
        return $this;
    }
    
    /**
     * Agrega las columnas a la fila e inserta otra
     * @param array $params
     */
    public function addRow(array $params = array()) {
        $this->rows[$this->pointer] = $params + array(
            'id' => NULL,
            'color' => NULL
        );
        
        ++ $this->pointer;
    }
}
