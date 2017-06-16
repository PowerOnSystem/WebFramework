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

namespace App\Controller;

use PowerOn\Controller\Controller;

/**
 * IndexController
 * @author Lucas Sosa
 * @version 0.1
 */
class IndexController extends Controller {

    public function index() {
        $table = new \PowerOn\Utility\Table(['border' => 1]);
        $content = [
            [
                '_row_param' => ['class' => 'warning'],
                'id' => '1', 'name' => 'Sergio', 
                'client' => ['title' => '00922893', 'link' => ['controller' => 'clients', 'action' => 'view', '12893']]
            ],
            [
                'id' => '2', 'name' => 'Alejo', 
                'client' => ['title' => '00128293', 'link' => ['controller' => 'clients', 'action' => 'view', '12893']]
            ],
            [
                '_row_param' => ['class' => 'warning'],
                'id' => '3', 'name' => 'Chanchi', 
                'client' => ['title' => '00128332', 'link' => ['controller' => 'clients', 'action' => 'view', '5235235']]
            ],
            [
                'id' => '4', 'name' => 'Miguel', 
                'client' => ['title' => '00128372', 'link' => ['controller' => 'clients', 'action' => 'view', '12312']]
            ],
            [
                'id' => '5', 'name' => 'Daniel', 
                'client' => ['title' => '00287382', 'link' => ['controller' => 'clients', 'action' => 'view', '35353']]
            ]
        ];
        
        $pagination = new \PowerOn\Utility\Pagination($this->request->url('page'), count($content), 2);
        
        $table->header([
            'id' => 'Codigo',
            'name' => 'Nombre',
            'client' => ['title' => 'Nro. Cliente', 'class' => 'po highlight', 'error' => 'ident']
        ]);
        $table->footer([
            'id' => ['colspan' => 2],
            'name' => NULL,
            'client' => '30 clientes'
        ]);
        $table->body($content);
        $this->view->set('table', $table);
    }

    public function error() {
        $this->view->set('errors', $this->exception);
    }

}
