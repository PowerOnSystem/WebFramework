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
use function \PowerOn\Application\html_serialize;
use function \PowerOn\Application\array_trim;

/**
 * Ayudante de Html
 * @author Lucas Sosa
 * @version 0.1
 */
class HtmlHelper extends Helper {
    
    /**
     * Javascript
     * @var array 
     */
    private $_js = [];
    /**
     * Estilos CSS
     * @var array 
     */
    private $_css = [];
    /**
     * Metas HTML5
     * @var array
     */
    private $_meta = [];
    
    /**
     * Crea un enlace
     * @param string $content el contenido del enlace
     * @param array $url la URL
     * @param array $options [Opcional] las opciones
     * @return string una etiqueta a
     */
    public function link($content, $url = [], array $options = []) {
        $cfg = [
            'href' => is_array($url) ? (
                key_exists('push', $url) ? $this->url->push($url['push']) : (
                key_exists('add', $url) || key_exists('remove', $url) ? 
                    $this->url->modify(
                        key_exists('add', $url) ? $url['add'] : [],
                        key_exists('remove', $url) ? $url['remove'] : []
                    ) : (
                        key_exists('generate', $url) ? $this->url->build($url, \PowerOn\Application\array_trim($url, 'generate')) :
                            (key_exists('mailto', $url) ? 'mailto:' . $url['mailto'] : $this->url->build($url))
                    )
            )) : ( $this->url->routeExist($url) ? $this->url->build([], $url) : $url )
        ] + $options;
        return '<a ' . html_serialize($cfg) . ' >' . $content . '</a>' . PHP_EOL;
    }
        
    /**
     * Agrega un archivo javascript o enlista los agregados
     * @param string $name [Opcional] el nombre del archivo si no se especifica name devuelve todos los archivos js incluidos
     * @param boolean $external [Opcional] Especifica si se trata de un archivo JS externo.
     * @return string una etiqueta script
     */
    public function js($name = NULL, $external = FALSE, array $options = []) {
        if ($name) {
            $this->_js[$name] = $options + [
                'src' => $external ? $name : PO_PATH_JS  . '/' . $name
            ];
        } else {
            return implode(PHP_EOL, array_map(function($value) {
                return '<script ' . html_serialize($value) . '></script>';
            }, $this->_js)) . PHP_EOL;
        }
    }
    
    /**
     * Agrega un archivo css o enlista los agregados
     * @param string $name [Opcional] el nombre del archivo si no se especifica name devuelve todos los archivos js incluidos
     * @param boolean $external [Opcional] Especifica si se trata de un archivo JS externo.
     * @return string una etiqueta link
     */
    public function css($name = NULL, $external = FALSE, array $options = []) {
        if ( $name ) {
            $this->_css[$name] = $options + [
                'href' => $external ? $name : PO_PATH_CSS  . '/' . $name,
                'rel' => 'stylesheet',
                'media' => 'screen'
            ];
        } else {
            return implode(PHP_EOL, array_map(function($file) {
                return '<link ' . html_serialize($file) . ' />';
            }, $this->_css)) . PHP_EOL;
        }
    }
    
    /**
     * Agrega una etiqueta META
     * @param string $name Nombre/Tipo de etiqueta meta
     * @param string $content Contenido
     * @return string La etiqueta formateada
     */
    public function meta($name, $content) {
        if ( $name ) {
            $this->_meta[$name] = ['name' => $name, 'content' => $content];
        } else {
            return implode(PHP_EOL, array_map(function($meta) {
                return '<meta name="' . $meta['name'] . '" content="' . $meta['content'] . '" />';
            }, $this->_meta)) . PHP_EOL;
        }
    }
    
    /**
     * Crea una imagen
     * @param string $name el nombre del archivo
     * @param array $options [Opcional] las opciones
     * @param boolean $external [Opcional] Especifica si se trata de una imagen externa
     * @return string una etiqueta img
     */
    public function img($name, array $options = [], $external = FALSE) {
        $cfg = $options + [
            'class' => ''
        ];
        
        return '<img src = "' . ($external ? $name : PO_PATH_IMG  . '/' . $name) . '" ' . html_serialize($cfg) . ' />' . PHP_EOL;
    }
    
    /**
     * Crea una lista simple u ordenada
     * @param array $list Array con la lista completa, puede ser un array multidimencional
     * @param array $options [Opcional] Opciones de la lista Ej: <code>$options = ['class' => 'my_list', 'id' => 'mylist1']</code>
     * @param array $item_options [Opcional] Opciones de un item específico Ej: <code> $item_options = [1 => ['class' => 'active']] </code>
     * @param type $type_list [Opcional] Tipo de lista, "ordered" para una lista ordenada o list
     * @return string
     */
    public function nestedList(array $list, array $options = [], array $item_options = [], $type_list = 'list') {
        $type = $type_list == 'list' ? 'ul' : 'ol';
        $r = '<' . $type . ' ' . html_serialize($options) . '>';
        foreach ($list as $key => $l) {
            $r .= is_array($l) ? $this->nestedList($l, key_exists($key, $item_options) ? $item_options[$key] : []) : 
                '<li ' . (key_exists($key, $item_options) ? html_serialize($item_options[$key]) : '') . ' >' . 
                    $l . 
                '</li>';
        }
        $r .= '</' . $type . '>';
        
        return $r;
    }
}
