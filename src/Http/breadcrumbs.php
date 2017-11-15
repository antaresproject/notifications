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

use Antares\Notifications\Model\Notifications;
use Antares\UI\Navigation\Breadcrumbs\Manager;
use Antares\UI\Navigation\Breadcrumbs\Generator;

/* @var $manager Manager */

$manager->register('notifications.index', function(Generator $generator) {
    $url    = handles('antares::notifications');
    $item   = $generator->push('notifications', trans('antares/notifications::messages.notification_templates'), $url)->getChild('notifications');
    $acl    = $generator->acl()->make('antares/notifications');

    if($acl->can('notifications-create') && $generator->url()->current() === $url) {
        $item->addItem('notifications-create', trans('antares/notifications::messages.notification_templates_create'), handles('antares::notifications/create'), 'zmdi-notifications-add');
    }
});

$manager->register('notifications.create', function(Generator $generator) {
    $generator->parent('notifications.index');
    $generator->push(trans('antares/notifications::messages.notification_templates_create'));
});

$manager->register('notifications.edit', function(Generator $generator, Notifications $notification) {
    $name   = $notification->name;
    $id     = $notification->id;

    $generator->parent('notifications.index');
    $generator->push(trans('antares/notifications::messages.notification_templates_edit', compact('id' ,'name')));
});

$manager->register('notifications.logs.index', function(Generator $generator) {
    $generator->push('notifications-logs', trans('antares/notifications::logs.notification_log'));
});
