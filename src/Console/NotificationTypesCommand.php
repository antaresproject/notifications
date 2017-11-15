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

namespace Antares\Notifications\Console;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Antares\Notifications\Model\NotificationTypes;

class NotificationTypesCommand extends BaseCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'notifications:types-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show list of available notification types';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $types   = NotificationTypes::all();
        $flatten = [];
        foreach ($types as $type) {
            $flatten[] = ['<info>' . $type->id . '</info>', '<fg=red>' . $type->name . '</fg=red>', '<info>' . $type->title . '</info>'];
        }

        if (count($flatten) > 0) {
            $this->table(['Id', 'Name', 'Title'], $flatten);
        } else {
            $this->error('No types found');
        }
    }

}
