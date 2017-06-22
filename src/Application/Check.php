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

//Si estan bien configuradas las carpetas de la aplicación
$folders = [
    'Application root' => PO_PATH_APP, 
    'Content source' => PO_PATH_APP_CONTENT, 
    'Configuration' => PO_PATH_CONFIG, 
    'Helpers' => PO_PATH_HELPER, 
    'Languages' => PO_PATH_LANGS, 
    'View templates' => PO_PATH_TEMPLATES, 
    'View' => PO_PATH_VIEW, 
    'Webroot' => PO_PATH_WEBROOT
];

foreach ($folders as $name => $folder) {
    if ( !is_dir($folder) ) {
        throw new \Exception(sprintf('No existe la carpeta (%s) configurada en (%s)', $name, $folder));
    }
}

$check = fopen(PO_PATH_APP . DS . 'check.lock.php', 'w');
fwrite($check, 'ok');
fclose($check);