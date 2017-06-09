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
use PowerOn\Utility\Hash;

/**
 * Controlador de sesiones de usario
 * @author Lucas Sosa
 * @version 0.1
 */
class Session {

    /**
     * Elimina todas las sesiones activas sin importar el ID
     */
    public function destroy() {
        $_SESSION = [];
        if ( ini_get("session.use_cookies") ) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
    
    /**
     * Obtiene un dato de sesion
     * @param string $name
     * @return mix El valor solicitado
     */
    public function read($name) {
        $var = Hash::get($_SESSION, $name);
        return $var;
    }
    
    /**
     * Escribe o modifica un valor en la sesion
     * @param string $name El nombre de la variable de sesion
     * @param mix $value El valor a escribir
     */
    public function write($name, $value) {
        $_SESSION = Hash::write($_SESSION, $name, $value);
        return $value;
    }
    
    /**
     * Inserta un nuevo valor de sesion
     * @param string $name El nombre de la variable de sesion a insertar
     * @param mix $value El nuevo valor
     */
    public function push($name, $value) {
        $_SESSION = Hash::insert($_SESSION, $name, $value);
        return $value;
    }
    
    /**
     * Obtiene y elimina un valor de sesion
     * @param string $name El nombre del valor a obtener
     * @return type
     */
    public function consume($name) {
        $value = $this->read($name);
        $this->remove($name);
        return $value;
    }
    
    /**
     * Elimina un valor de una sesion
     * @param string $name El nombre de la variable de sesion a eliminar
     */
    public function remove($name) {
        $_SESSION = Hash::remove($_SESSION, $name);
    }
}
