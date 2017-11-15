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
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

use Illuminate\Routing\Router;

/* @var $router Router */

$router->model('notification', \Antares\Notifications\Model\Notifications::class);

$router->group(['prefix' => 'notifications'], function (Router $router) {
    $router->match(['GET', 'POST'], '/', 'IndexController@index')->name('notifications.index');

    $router->get('create', 'IndexController@create')->name('notifications.create');
    $router->post('store', 'IndexController@store');

    $router->get('{notification}/edit', 'IndexController@edit')->name('notifications.edit');
    $router->post('{notification}/update', 'IndexController@update');

    $router->post('{notification}/sendTest', 'IndexController@sendTestOfNotification');
    $router->post('sendTest', 'IndexController@sendTest');
    $router->post('preview', 'IndexController@preview');
    $router->post('disable', 'IndexController@massDisable');
    $router->post('enable', 'IndexController@massEnable');

    $router->post('{notification}/changeStatus', 'IndexController@changeStatus');

    $router->delete('{notification}', 'IndexController@destroy');

    $router->group(['prefix' => 'sidebar'], function (Router $router) {
        $router->post('delete', 'SidebarController@delete');
        $router->post('read/{type?}', 'SidebarController@read');
        $router->get('get', 'SidebarController@get');
        $router->get('clear/{type?}', 'SidebarController@clear');
    });

    $router->group(['prefix' => 'logs'], function (Router $router) {
        $router->match(['GET', 'POST'], '/', 'LogsController@index')->name('notifications.logs.index');
        $router->get('{id}/preview', 'LogsController@preview');
        $router->delete('{id}/delete', 'LogsController@delete');
        $router->post('delete', 'LogsController@delete');
    });

});


