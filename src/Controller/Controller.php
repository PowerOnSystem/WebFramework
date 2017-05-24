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

namespace PowerOn\Controller;

use PowerOn\View\View;
use PowerOn\Network\Request;
use PowerOn\Routing\Router;
use PowerOn\Core\Core;

/**
 * Controlador
 * @version 0.1
 * @author Lucas
 */
class Controller {
    /**
     * Control del template
     *  @var View 
     */
    protected $view;
    /**
     * Todos los datos de la solicitud del cliete
     *  @var Request
     */
    protected $request;
    /** 
     * El Router encargado de la URL
     * @var Router
     */
    protected $router;

    /**
     * Inicializa un controlador
     * @param View $view
     * @param Request $request
     * @param Router $router
     * @param Core $core
     */
    public function initialize(View $view, Request $request, Router $router) {
        $this->view = $view;
        $this->request = $request;
        $this->router = $router;
    }
}