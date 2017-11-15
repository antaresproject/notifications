<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Notifications
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

use Illuminate\Routing\Router;

$router->group(['prefix' => 'notifications'], function (Router $router) {
    /** sidebar * */
    $router->post('sidebar/delete', 'SidebarController@delete');
    $router->post('sidebar/read/{type?}', 'SidebarController@read');
    $router->get('sidebar/get', 'SidebarController@get');
    $router->get('sidebar/clear/{type?}', 'SidebarController@clear');
});


