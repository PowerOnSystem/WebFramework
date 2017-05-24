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

namespace PowerOn\View\Helper;
use  PowerOn\Routing\Router;

/**
 * Ayudante de Html
 * @author Lucas Sosa
 * @version 0.1
 */
class HtmlHelper {
    
    /**
     * Javascript
     * @var array 
     */
    public $js = array();
    /**
     * Javascripts externos
     * @var array
     */
    public $ext_js = array();
    /**
     * Estilos
     * @var array 
     */
    public $css = array();
    /**
     * Router
     * @var Router
     */
    private $_router;
    
    public function __construct(Router $router) {
        $this->_router = $router;
    }
    /**
     * Crea un enlace
     * @param string $content el contenido del enlace
     * @param array $url la URL
     * @param array $options [Opcional] las opciones
     * @return string una etiqueta a
     */
    public function link($content, $url = array(), array $options = array()) {
        $cfg = array(
            'href' => is_array($url) ? (
                key_exists('push', $url) ? $this->_router->pushUrl($url['push']) : (
                key_exists('add', $url) || key_exists('remove', $url) ? 
                    $this->_router->modifyUrl(
                        key_exists('add', $url) ? $url['add'] : array(),
                        key_exists('remove', $url) ? $url['remove'] : array()
                    ) : (
                        key_exists('mailto', $url) ? 'mailto:' . $url['mailto'] : $this->_router->buildUrl($url)
                    )
            )) : $url
        ) + $options;
        return '<a ' . \PowerOn\Core\PowerOnSerialize($cfg) . ' >' . $content . '</a>';
    }
    
    /**
     * Crea un texto con etiqueta span
     * @param string $class Clase de texto
     * @param string $text El texto
     * @param array $options [Opcional] Opciones
     * @return string
     */
    public function text($class, $text, array $options = array()) {
        $cfg = $options + array(
            'class' => 'cnc text ' . $class,
        );
        return '<span ' . \PowerOn\Core\PowerOnSerialize($cfg) . '>' . $text . '</span>';
    }
    
    /**
     * Crea una lista ul
     * @param array $list
     * @param array $options
     * @return string
     */
    public function items(array $list, array $options = array()) {
        $cfg = $options + array(
            'class' => 'ui list'
        );
        
        $r = '<ul ' . \PowerOn\Core\PowerOnSerialize($cfg) . '>';
        $r .= '<li class = "item">' . implode('</li><li class = "item">', $list) . '</li>';
        $r .= '</ul>';
        
        return $r;
    }
    
    public function menu($text, array $items, array $options = array()) {
        $cfg = array(
            'class' => 'ui dropdown ' . (key_exists('class', $options) ? $options['class'] : '' )
        ) + $options;
        $r = '<div ' . \PowerOn\Core\PowerOnSerialize($cfg) . '>';
            $r .= $text;
            $r .= '<div class = "menu">';
            foreach ($items as $i) {
                $cfgitem = (key_exists('options', $i) ? $i['options'] : array() ) + array(
                    'class' => (isset($i['options']['class']) ? $i['options']['class'] : '') . ' item'
                );
                $r .= '<div ' . \PowerOn\Core\PowerOnSerialize($cfgitem) . '>';
                    $r .= (key_exists('content', $i) ? $i['content'] : '' );
                $r .= '</div>';
            }
            $r .= '</div>';
        $r .= '</div>';
         
        return $r;
    }
    
    /**
     * Agrega un archivo javascript o enlista los agregados
     * @param string $name [Opcional] el nombre del archivo si no se especifica name devuelve todos los archivos js incluidos
     * @return string una etiqueta script
     */
    public function js($name = NULL) {
        if ($name) { 
            return '<script src = "' . (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '')  . '/js/' . $name . '"></script>';
        } else {
            $s = '';
            foreach ($this->js as $j) {
                $s .= '<script src = "' . (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '')  . '/js/' . $j . '"></script>' . PHP_EOL;
            }
            foreach ($this->ext_js as $j) {
                $s .= '<script src = "' . $j . '"></script>' . PHP_EOL;
            }
            return $s;
        }
    }
    
    /**
     * Agrega un archivo css o enlista los agregados
     * @param string $name [Opcional] el nombre del archivo si no se especifica name devuelve todos los archivos js incluidos
     * @param array $options [Opcional] las opciones del archivo css
     * @return string una etiqueta link
     */
    public function css($name = NULL, array $options = array()) {
        if ($name) {
            $cfg = $options + array(
                'href' => (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '')  . '/css/' . $name,
                'rel' => 'stylesheet',
                'media' => 'screen'
            );
            return '<link ' . \PowerOn\Core\PowerOnSerialize($cfg) . ' />';
        } else {
            $s = '';
            foreach ($this->css as $c) {
                $s .= '<link ' . \PowerOn\Core\PowerOnSerialize($c) . ' />';
            }
            return $s;
        }
    }
    
    /**
     * Agrega un archivo javascript para enlistar luego
     * @param string $name el nombre del archivo
     * @param boolean $external [Opcional] Si es TRUE especifíca si el archivo es externo
     */
    public function addJs($name, $external = FALSE) {
        if ( $external ) {
            $this->ext_js[] = $name;
        } else {
            $this->js[$name] = $name;
        }
    }
    
    /**
     * Agrega un archivo css para enlistar luego
     * @param string $name el nombre del archivo
     * @param array $options [Opcional] las opciones de la etiqueta
     */
    public function addCss($name, array $options = array()) {
        $this->css[$name] = $options + array(
            'href' => (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '')  . '/css/' . $name,
            'rel' => 'stylesheet',
            'media' => 'screen'
        );
    }
    
    /**
     * Crea un icono
     * @param string $name el nombre del icono
     * @return string una etiqueta span
     */
    public function icon($name){
        return '<i class = "icon ' . $name . '" aria-hidden = "true"></i>';
    }
    
    /**
     * Crea un icono
     * @param string $name el nombre del icono
     * @return string una etiqueta span
     */
    public function spinner($name){
        return '<i class = "fa fa-spin fa-fw fa-' . $name . '"></i><span class = "sr-only">Loading...</span>';
    }
    
    /**
     * Crea una imagen
     * @param string $name el nombre del archivo
     * @param array $options [Opcional] las opciones
     * @return string una etiqueta img
     */
    public function img($name, array $options = array()) {
        $cfg = $options + array(
            'class' => 'po img'
        );
        return '<img src = "' . (CNC_DIR_ROOT ? '/' . CNC_DIR_ROOT : '')  . '/img/' . $name . '" ' . \PowerOn\Core\PowerOnSerialize($cfg) . ' />';
    }
    
    /**
     * Crea una imagen
     * @param string $path La ruta directa con el nombre completo
     * @param array $options [Opcional] las opciones
     * @return string una etiqueta img
     */
    public function imgPath($path, array $options = array()) {
        $cfg = $options + array(
            'class' => 'po img'
        );
        return '<img src = "' . $path . '" ' . \PowerOn\Core\PowerOnSerialize($cfg) . ' />';
    }
    
    /**
     * Crea una imagen con thumbnail
     * @param string $name el nombre del archivo
     * @param array $options [Opcional] las opciones
     * @return string una etiqueta img
     */
    public function imgThumb($path, $file, array $options = array()) {
        $cfg = $options;
        return '<img src = "' . $path . '/thumbs/' . $file . '" ' . \PowerOn\Core\PowerOnSerialize($cfg) . ' '
                . 'onclick = "maximize_image(\'' . $path . '/' . $file . '\')" />';
    }
    
    /**
     * Crea el encabezado del modulo
     * @param \PowerOn\Database\Entity $action
     * @return string
     */
    public function header(\PowerOn\Database\Entity $action) {
        return $this->img('icons/' . ($action->icon ? $action->icon : 'page.png')) . ' ' . $action->title;
    }
}
