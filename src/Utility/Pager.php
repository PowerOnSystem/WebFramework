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

/**
 * Inflector
 * @author Lucas Sosa
 * @version 0.1
 */
class Pager {
    public $current_page;
    public $results_per_page;
    public $max_results;
    public $show_results;
    public $number_pages;
    public $next;
    public $previous;
    public $next_ten_pages;
    public $previous_ten_pages;
    public $show_first_page;
    public $show_last_page;
    public $start_pagination;
    public $end_pagination;
    public $mysql_start_results;
    public $mysql_end_results;
    
    /**
     * Obtiene todos los datos para realizar la paginacion de resultados
     * @param integer $current_page La pagina actual
     * @param integer $num_rows Los resultados maximos
     * @return string Devuelve una cadena con el limit de mysql 
    */
    public function process($current_page, $num_rows, $results_per_page) {
        $number_of_pages = intval(ceil($num_rows / $results_per_page));
        $page = intval($current_page < $number_of_pages ? $current_page : $number_of_pages);
        $cp = $page ? $page : 1;
        $start_pagination = intval($cp / 10) * 10;
        $end_pagination = $start_pagination ? $start_pagination + 10 : 10;

        $this->current_page = $cp;
        $this->results_per_page = $results_per_page;
        $this->max_results = $num_rows;
        $this->show_results = $number_of_pages == $cp ? ($num_rows % $results_per_page) : $results_per_page;
        $this->number_pages = $number_of_pages;
        $this->next = $cp < $number_of_pages ? TRUE : FALSE;
        $this->previous = $cp > 1 ? TRUE : FALSE;
        $this->next_ten_pages = $cp < $number_of_pages - 10 ? $cp + 10 : FALSE;
        $this->previous_ten_pages = $cp >= 20 ? $cp - 10 : FALSE;
        $this->show_first_page = $cp >= 10 ?  TRUE : FALSE;
        $this->show_last_page = $number_of_pages > 10 && $end_pagination < $number_of_pages ? TRUE : FALSE;
        $this->start_pagination = $start_pagination ? $start_pagination : 1;
        $this->end_pagination = $end_pagination < $number_of_pages ? $end_pagination : $number_of_pages;
        $this->mysql_start_results = $cp > 1 ? ($cp - 1) * $results_per_page : 0;
        $this->mysql_end_results = $results_per_page;
    }
}
