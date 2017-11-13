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


namespace Antares\Notifications\Console;

use Antares\Notifications\Services\EventsRegistrarService;
use Illuminate\Database\Console\Migrations\BaseCommand;
use Antares\Notifications\Model\NotificationCategory;

class NotificationCategoriesCommand extends BaseCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'notifications:category-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show list of available notification categories';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        /* @var $service EventsRegistrarService */
        $service    = app()->make(EventsRegistrarService::class);
        $categories = $service->getEventsCategories();
        $flatten    = [];

        foreach ($categories as $category) {
            $flatten[] = ['<info>' . $category['id'] . '</info>', '<info>' . $category['label'] . '</info>'];
        }

        if (count($flatten) > 0) {
            $this->table(['Id', 'Label'], $flatten);
        } else {
            $this->error('No categories found');
        }
    }

}
