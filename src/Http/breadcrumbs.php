<?php

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
