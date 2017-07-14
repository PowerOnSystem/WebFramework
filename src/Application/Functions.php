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

namespace PowerOn\Application;

/**
 * Encripta una cadena
 * @param string $words La cadena a encriptar
 * @return string La cadena encriptada
 */
function poweron_crypt($words, $private_key) {
    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($private_key), $words, MCRYPT_MODE_CBC, md5(md5($private_key))));
    return $encrypted;
 
}
/**
 * Desencripta una cadena
 * @param string $words La cadena a desencriptar
 * @return string la cadena desencriptada
 */
function poweron_decrypt($words, $private_key){
     $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($private_key),
             base64_decode($words), MCRYPT_MODE_CBC, md5(md5($private_key))), "\0");
    return $decrypted;
}

/**
 * Encripta un password utilizando las funciones de php
 * @param String $password el password a encriptar
 * @param Integer $digit el numero de digitos
 * @return String Devuelve el password encriptado
 */
function crypt_blowfish($password, $digit = 7) {
    $set_salt = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $salt = sprintf('$2a$%02d$', $digit);
    for($i = 0; $i < 22; $i++) {
        $salt .= $set_salt[mt_rand(0, 63)];
    }
    
    return crypt($password, $salt);
}

/**
 * Comprueba que el password sea correcto utilizando el metodo crypt
 * @param String $input El password enviado por el usuario
 * @param String $saved El password guardado en db
 * @return Boolean Devuelve True en caso de coincidir o False en caso contrario
 */
function test_crypt($input, $saved) {
    return crypt($input, $saved) == $saved;
}