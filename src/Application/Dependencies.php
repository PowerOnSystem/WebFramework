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

return [
    'PowerOn\Routing\Dispatcher' => ['PowerOn\Routing\Router', 'PowerOn\Network\Request', 'PowerOn\View\View'],
    'PowerOn\View\View' => ['PowerOn\View\Helper\HtmlHelper', 'PowerOn\View\Helper\FormHelper', 'PowerOn\View\Helper\BlockHelper'],
    'PowerOn\View\Helper\HtmlHelper' => ['PowerOn\Routing\Router'],
    'PowerOn\View\Helper\BlockHelper' => ['PowerOn\View\Helper\HtmlHelper', 
        'PowerOn\View\Helper\FormHelper', 'PowerOn\Network\Request', 'PowerOn\Routing\Router'],
    'PowerOn\View\Helper\FormHelper' => ['PowerOn\Routing\Router', 'PowerOn\View\Helper\HtmlHelper'],
    'PowerOn\Routing\Router' => ['PowerOn\Network\Request'],
    'PowerOn\Network\Request' => [],
    'PowerOn\Controller\Controller::initialize' => ['PowerOn\View\View', 'PowerOn\Network\Request', 'PowerOn\Routing\Router', 'Monolog\Logger']
];
