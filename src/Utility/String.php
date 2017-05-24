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

namespace CNCService\Utility;

/**
 * String
 * @author Lucas Sosa
 * @version 0.1
 * @copyright (c) 2016, Lucas Sosa
 */
class String {
    
    /**
     * Corta una cadena en palabras
     * @param string $words
     * @param integer $limit
     * @return string
     */
    public static function cut($words, $limit = 10) {
        $array_words = explode(' ', $words);
        $count = 0;
        $result = array();
        foreach ($array_words as $a) {
            $count += strlen($a) + 1;
            $result[] = $a;
            if ( $count >= $limit ) {
                break;
            }
        }

        return implode(' ', $result);
    }
    
}
