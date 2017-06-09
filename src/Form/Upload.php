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

/**
 * Upload de archivos en formularios
 *
 * @author Lucas Sosa
 * @version 0.1
 */
class Upload {
    /**
     * El peso del archivo en bytes
     * @var integer
     */
    public $size;
    /**
     * La ubicación temporal del archivo
     * @var string 
     */
    public $location;
    /**
     * El nombre original
     * @var string
     */
    public $name;
    /**
     * El tipo de mime
     * @var string 
     */
    public $type;
    /**
     * La Extensión
     * @var string 
     */
    public $extension;
    /**
     * El nombre final
     * @var string 
     */
    public $filename;
    /**
     * Prefijo para subida multiple de archivos
     * @var string 
     */    
    public $prefix;
    /**
     * Ubicacion del archivo real
     * @var string
     */
    public $path;
    /**
     * El numero de error encontrado
     * @var integer 
     */
    public $status = NULL;
    /**
     * Thumbnail de la imagen subida
     * @var object 
     */
    public $thumbnail;
    /**
     * Ancho del thumbnail
     * @var integer
     */
    public $thumbnail_width;
    /**
     * Altura del thumbnail
     * @var integer
     */
    public $thumbnail_height;
    /**
     * Imagen subida
     * @var object
     */
    public $image;
    /**
     * Ancho de la imagen
     * @var integer 
     */
    public $image_width;
    /**
     * Altura de la imagen
     * @var integer
     */
    public $image_height;
    /**
     * Tipo de imagen
     * @var string
     */
    public $image_type;
    /**
     * Destino donde se a a guardar los datos
     * @var string
     */
    public $destiny;
    
    /**
     * Maneja la subida de archivos al servidor
     * @param integer $size Tamaño del archivo
     * @param string $tmp_name Ubicacion temporal
     * @param string $name Nombre
     * @param string $type Tipo de archivo
     * @param integer $status Código de error
     */
    public function __construct($size = NULL, $tmp_name = NULL, $name = NULL, $type = NULL, $status = NULL) {

        $this->size = $size;
        $this->location = $tmp_name;
        $this->name = $name;
        $this->type = $type;
        $this->status = $status;
        $this->extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));
        
    }
    
    /**
     * Verifica si el archivo se subió correctamente
     * @return boolean
     */
    public function isUploaded() {
        return $this->status === UPLOAD_ERR_OK;
    }
    
    /**
     * Crea un nombre genérico para el archivo
     */
    public function createFileName($prefix = NULL) {
        $this->filename = uniqid($prefix) . mt_rand(1, 999) . '.' . $this->extension;
    }
    
    /**
     * Redimenciona una imagen especificando la relación de aspecto
     * @param integer $measure Medida en relacion de aspecto
     */
    public function resize($measure) {
        $source = $this->location;
        $image_info = getimagesize($source);

        if ( !$image_info ) {
            throw new \Exception('No se pudo obtener la informaci&oacute;n de la imagen');
        }

        $this->image_width = $image_info[0];
        $this->image_height = $image_info[1];
        $this->image_type = $image_info[2];

        switch( $this->image_type ) {
            case IMAGETYPE_JPEG: {
                $this->image = imagecreatefromjpeg($source);
                $this->extension = 'jpg';
                break;
            }

            case IMAGETYPE_GIF: {
                $this->image = imagecreatefromgif($source);
                $this->extension = 'gif';
                break;
            }

            case IMAGETYPE_PNG: {
                $this->image = imagecreatefrompng($source);
                $this->extension = 'png';
                break;
            }

            default: {
                throw new \Exception(sprintf('El formato (%s) no es soportado.', $this->image_type));
            }
        }
        
        if ( $this->image_width > $measure && $this->image_width >= $this->image_height ) {
            $relation = $this->image_width / $this->image_height;
            $height = intval($measure / $relation);
            $width = $measure;
        } else {
            if ( $this->image_height > $measure && $this->image_width <= $this->image_height ) {
                $relation = $this->image_height / $this->image_width;
                $height = $measure;
                $width = intval($measure / $relation);
            } else {
                $height = $this->image_height;
                $width = $this->image_width;
            }
        }
        $this->thumbnail_width = $width;
        $this->thumbnail_height = $height;
        
        $this->thumbnail = imagecreatetruecolor($this->thumbnail_width, $this->thumbnail_height);

        imagecopyresampled(
            $this->thumbnail, $this->image, 0, 0, 0, 0,
            $this->thumbnail_width, $this->thumbnail_height,
            $this->image_width, $this->image_height
        );
    }
    
    /**
     * Guarda el archivo en el destino especificado
     * @param string $destiny Carpeta destino
     */
    public function save($destiny) {
        if ( empty($this->filename) ) {
            $this->createFileName();
        }
        $this->destiny = $destiny;
        $this->path = $this->destiny . DS . $this->filename;
        if ( $this->image ) {
            switch( $this->image_type ) {
                case IMAGETYPE_JPEG: imagejpeg($this->image, $this->path, 95); break;
                case IMAGETYPE_PNG:
                case IMAGETYPE_GIF: imagegif($this->image, $this->path); break;
            }
            imagedestroy($this->image);
        } else if ( is_file($this->location) && !move_uploaded_file($this->location, $this->path) ) {
            throw new \Exception(sprintf('No se puede subir el archivo (%s) a la ruta (%s)', $this->filename, $this->path));
        }
        
        return TRUE;
    }
    
    /**
     * Guarda el thumbnail de la imagen
     * @param string $destiny [Opcional] Si se omite se guarda en el mísmo destino de la imagen original
     * @param string $prefix [Opcional] Prefijo del nombre (Si el destino esta omitido entonces este campo es obligatorio)
     * @throws \Exception
     */
    public function saveThumbnail($destiny = NULL, $prefix = 'thumb_') {
        if ( empty($this->filename) ) {
            $this->createFileName();
        }
        
        if ( !$destiny && !$$this->destiny ) {
            throw new \Exception('Debe proporcionar un destino correcto');
        }
        
        if (!$prefix && !$destiny) {
            throw new \Exception('El prefijo es obligatorio cuando la imagen se guarda en el mismo destino que la original');
        }
        
        $thumb_path = ($destiny ? $destiny : $this->destiny) . DS . $prefix . $this->filename;
        switch( $this->image_type ) {
            case IMAGETYPE_JPEG: imagejpeg($this->thumbnail, $thumb_path, 95); break;
            case IMAGETYPE_PNG:
            case IMAGETYPE_GIF: imagegif($this->thumbnail, $thumb_path); break;
        }
        imagedestroy($this->thumbnail);
    }
}