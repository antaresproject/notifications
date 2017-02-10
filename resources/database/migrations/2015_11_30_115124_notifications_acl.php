<?php

/**
 * Part of the Antares Project package.
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
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */


use Antares\Model\Role;
use Illuminate\Database\Migrations\Migration;

class NotificationsAcl extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $admin  = Role::admin();
        $acl    = app('antares.acl')->make('antares/notifications');
        $memory = app('antares.memory')->make('component');
        $acl->attach($memory);

        $acl->roles()->attach([$admin->name]);
        $presentationActions = [
            'Notifications List', 'Notifications Details', 'Notifications Preview'
        ];
        $crudActions         = [
            'Notifications Edit', 'Notifications Duplicate', 'Notifications Test', 'Notifications Change Status', 'Notifications Create',
            'Widget Send Notification', 'Notifications Delete'
        ];
        $acl->actions()->attach(array_merge($presentationActions, $crudActions));
        $acl->allow($admin->name, array_merge($presentationActions, $crudActions));


        $memory->finish();
        app('antares.memory')->make('primary')->getHandler()->forgetCache();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Foundation::memory()->forget('acl_antares/notifications');
    }

}
