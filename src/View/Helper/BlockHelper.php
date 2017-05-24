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

namespace PowerOn\View\Helper;
use PowerOn\Utility\Table;
use PowerOn\Utility\Moment;
use PowerOn\Network\Request;
use PowerOn\Routing\Router;

/**
 * BlockHelper
 * Maneja los bloques de codigo
 * @author Lucas Sosa
 * @version 0.1
 */
class BlockHelper {
    /**
     * Ayudante de HTML para el Block
     * @var HtmlHelper
     */
    private $_html;
    /**
     * Ayudante de Formulario para el Block
     * @var FormHelper
     */
    private $_form;
    /**
     * Solicitud del cliente
     * @var Request
     */
    private $_request;
    /**
     * Router del sistema
     * @var Router
     */
    private $_router;
    
    public function __construct(HtmlHelper $html, FormHelper $form, Request $request, Router $router) {
        $this->_html = $html;
        $this->_form = $form;
        $this->_request = $request;
        $this->_router = $router;
    }
    
    public function table(Table $table) {
        $r = '';
        if ( $table->body ) {
            $r = '<table class = "ui cnc striped ' . $table->class . ($table->sort ? ' sortable' : '') . ' small compact table">';
            if ( $table->header ) {
                $r .= '<thead>';
                    $r .= '<tr>';
                    foreach ($table->header as $name => $h) {
                        if ( $table->sort ) {
                            $h['class'] .= $table->sort_by == $name ? ($table->sort_mode == 'desc' ? 'sorted descending' : 'sorted ascending') : '';
                        }
                        $r .= '<th width = "' . $h['width'] . '" class = "' . $h['class'] . '">';
                        if ( $table->sort ) {
                            $r .= $this->_html->link($h['title'], [
                                    'add' => ['query' => [
                                            'sort_by' => key_exists('sort_field', $h) ? $h['sort_field'] : $name,
                                            'sort_mode' => $table->sort_mode == 'desc' ? 'asc' : 'desc'
                                        ]
                                    ]
                                ], ['class' => 'sort_table']
                            );
                        } else {
                            $r .= $h['title'];
                        }
                        $r .= '</th>';
                    }
                    $r .= '</tr>';
                $r .= '</thead>';
            }
                $r .= '<tbody>';
                foreach ($table->body as $pointer => $field) {
                    $r .= '<tr class = "' . $table->rows[$pointer]['color'] . '">';
                    foreach ($field as $name => $c) {
                        $r .= '<td class = "' . $c['color'] . '" >';
                        $result = '';
                        switch ($c['element']) {
                            case 'join'     : $result = $this->_html->link($this->_html->icon('play'), $c['url']); break;
                            case 'address'  : $result = $this->address($c['address'], $c['locality'], $c['state'],
                                    $c['city'], $c['country'], $c['zip']); break;
                            case 'contact'  : $result = $this->contact($c['contact']); break;
                            case 'phones'   : $result = $this->phones($c['phones'], $c['prefix'], 1); break;
                            case 'email'    : $result = $this->email($c['email']); break;
                            default         : $result = $c['content']; break;
                        }

                        $r .= key_exists('words_cut', $table->header[$name]) ?
                                $this->word_cut($result, $table->header[$name]['words_cut']) : 
                                $result;
                        $r .= '</td>';
                    }
                    $r.= '</tr>';
                }
                $r.= '</tbody>';
            $r.= '</table>';
        } else {
            $r .= '<div class = "ui message icon info">';
                $r .= '<i class = "icon info"></i>';
                $r .= '<div class = "content">';
                    $r .= '<div class = "header">';
                        $r .= 'No hay registros disponibles ' . ($table->fast_search ? 'con la palabra "' . $table->fast_search . '"' : '');
                    $r .= '</div>';
                    $r .= '<p>No hay elementos disponibles de la lista solicitada</p>';
                $r .= '</div>';
            $r .= '</div>';
        }
        
        if ( $table->pager && $table->pager->number_pages > 1) {
            $r .= '<div class = "ui centered grid">';
                $r .= '<div class = "center aligned column">';
                    $r .= '<div class = "ui pagination menu compact">';
                    if ( $table->pager->show_first_page ) {
                        $r .= $this->_html->link('<strong>1</strong>', ['add' => ['query' => ['page' => 1]]], ['class' => 'item']);
                        $r .= $table->pager->previous_ten_pages ? 
                                $this->_html->link('...', ['add' => 
                                    ['query' => ['page' => $table->pager->previous_ten_pages]]], ['class' => 'item']
                                ) : 
                                '<div class = "disabled item">...</div>';
                    }
                    for ( $i = $table->pager->start_pagination; $i <= $table->pager->end_pagination; ++$i ) {
                        $class = 'item ' . ($i == $table->pager->current_page ? 'active' : '');
                        $r .= $this->_html->link($i, ['add' => ['query' => ['page' => $i]]], ['class' => $class]);
                    }
                    if ( $table->pager->show_last_page ) {
                        $r .= $table->pager->next_ten_pages ? 
                                $this->_html->link('...', array('add' => array('query' => array('page' => $table->pager->next_ten_pages))), 
                                        array('class' => 'item')) : 
                                '<div class = "disabled item">...</div>';
                        $r .= $this->_html->link('<strong>' . $table->pager->number_pages . '</strong>',
                                array('add' => array('query' => array('page' => $table->pager->number_pages))), array('class' => 'item'));
                    }

                    $r .= '</div>';
                $r .= '</div>';
            $r .= '</div>';
        }
        
        return $r;
    }
    
    public function tableOptions(Table $table) {
        $r = '<div class = "ui teal segment">';
            $r .= '<div class = "ui horizontal list divided">';
            
                if ( $table->fast_search ) {
                    $r .= '<div class = "item">';
                        $r .= '<i class = "icon search"></i>';
                        $r .= '<div class = "content">';
                            $r .= 'Filtrando con la palabra <strong>' . $table->fast_search . '</strong> ';
                            $r .= $this->_html->link($this->_html->icon('red close'), 
                                    array('remove' => array('query' => array('search', 'page'))), array('title' => 'Quitar b&uacute;squeda'));
                        $r .= '</div>';
                    $r .= '</div>';
                }
                
                $results = count($table->rows);
                if ( $results ) {
                    $r .= '<div class = "item">';
                        $r .= '<i class = "icon table"></i>';
                        $r .= '<div class = "content">';
                        if ( $table->pager && $table->pager->number_pages > 1) {
                            $r .= 'Mostrando <strong>' . $results . 
                                '</strong> de <strong>' . $table->pager->max_results . '</strong> resultado(s)';
                        } else {
                            $r .= 'Mostrando <strong>' . $results . '</strong> resultado(s)';
                        }
                        $r .= '</div>';
                    $r .= '</div>';
                }
                
                if ( $table->sort_by ) {
                    $r .= '<div class = "item">';
                        $r .= '<i class = "icon sort content ' . ($table->sort_mode == 'asc' ? 'ascending' : 'descending') . '"></i>';
                        $r .= '<div class = "content">';
                            $field = key_exists($table->sort_by, $table->header) ? 
                                    $table->header[$table->sort_by]['title'] : 
                                    \PowerOn\Core\PowerOnMultiArraySearch($table->header, 'sort_field', $table->sort_by);
                            $r .= 'Ordenado por <strong>' . $field . '</strong> ';
                            $r .= $this->_html->link($this->_html->icon('red close'), 
                                    array('remove' => array('query' => array('sort_by', 'sort_mode'))), array('title' => 'Quitar orden'));
                        $r .= '</div>';
                    $r .= '</div>';
                }
                
                if ( $table->pager && $table->pager->number_pages > 1) {
                    $r .= '<div class = "item">';
                        $r .= '<i class = "icon book"></i>';
                        $r .= '<div class = "content">';
                            $r .= 'P&aacute;gina <strong>' . $table->pager->current_page . 
                                '</strong> de <strong>' . $table->pager->number_pages . '</strong>';
                        $r .= '</div>';
                    $r .= '</div>';
                }
            $r .= '</div>';
        $r .= '</div>';
        return $r;
    }
    
    public function email($email) {
        return $email ? $this->_html->link($this->_html->icon('outline mail') . ' ' . $email, array('mailto' => $email)) : $this->notAvailable('Email');
    }
    
    public function winNumber($win) {
        return $win ? substr($win, 0,2) . '-' . substr($win, 2,8) . '-' . substr($win, 9) : $this->notAvailable('C.U.I.T.');
    }
    
    public function date($date) {
        $m = new Moment($date);
        return $date ? $this->_html->text('black', $this->_html->icon('calendar') . ' ' . $m->format(CNC_DATE_TIME_FORMAT), array('title' => $m->to())) : $this->notAvailable('Fecha');
    }
    
    public function dateHuman($date) {
        $m = new Moment($date);
        return $date ? $this->_html->text('black', $this->_html->icon('calendar') . ' <span>' . $m->humanize() . '</span>', array('title' => $m->format(CNC_DATE_TIME_FORMAT))) : $this->notAvailable('Fecha');
    }
    
    public function dateDiff($date_start, $date_end) {
        if (!$date_start || !$date_end) {
            return $this->notAvailable('Tiempo');
        }
        $s = new Moment($date_start);
        $e = new Moment($date_end);
        return $this->_html->text('black', $this->_html->icon('calendar') . ' ' . $s->to($e, FALSE), array('title' => $s->toHumanize($e)));
    }
    
    public function dateAgoRemain($date, $alert_time = FALSE) {
        $m = new Moment($date);
        $alert = $alert_time && $m->isBefore($alert_time); 
        return $date ? $this->_html->text($alert ? 'red' : 'black', $this->_html->icon('calendar') . ' ' . $m->to(), array('title' => $m->format(CNC_DATE_TIME_FORMAT))) : $this->notAvailable('Tiempo');
    }
    
    public function notAvailable($title = '') {
        return '<span class = "cnc text red"><i class = "icon attention"></i>' . $title . ' no disponible</span>';
    }
    
    public function googleMap($name, $latitude, $longitude, $class = '') {
        $this->_html->addJs('https://maps.googleapis.com/maps/api/js?key=AIzaSyBl6VClpYq4yxc_fQex8SS33t999KqYLiI&libraries=places&language=es&region=AR', TRUE);
        $r = '<div 
                id = "' . $name . '"
                latitude = "' . ($latitude ? $latitude : '') . '" 
                longitude = "' . ($longitude ? $longitude : '') . '" 
                class = "ui loading segment ' . $class . '"
            >';
        $r .= '</div>';
        
        return $r;
    }
}
