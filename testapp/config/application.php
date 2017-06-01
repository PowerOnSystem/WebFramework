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

/*
 * Configuracion de la aplicación completa y los servicios instalados.
 * En esta sección se configuran todos los servicios utilizados.
 * Por ejemplo, si se está utilizando PowerOnDBService:
 * return [
 *      'PowerOnDBService' => [
 *          'host' => 'localhost',
 *          'user' => ...
 *      ]
 * ]
 */
return [
    'View' => [
        'helper' => [
           /*
            * Helpers de templates, para reemplazar los existentes (html,
            * block y form), solo basta con colocar su namespace
            *  y el nombre, ejemplo: 'html' => 'Mi\Propio\Helper\Html' 
           */
        ]
    ]
];