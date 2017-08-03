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
use Symfony\Component\Finder\Finder;
use Antares\Extension\Manager;

class NotificationsImportCommand extends BaseCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'notifications:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import notification messages';

    /**
     * Extensions manager instance
     *
     * @var Manager
     */
    protected $manager;

    /**
     * Construct
     * 
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();
        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $files = $this->getFiles();
        vdump();
        exit;
        vdump(321);
        exit;
    }

    protected function getFiles()
    {
        $dirs = $this->getDirs();
        $dirs->filter(function(Finder $finder) {
            foreach ($finder as $file) {
                vdump($file);
                exit;
            }
        });
    }

    /**
     * Gets directories with notifications
     * 
     * @return \Illuminate\Support\Collection
     */
    protected function getDirs()
    {
        $extensions = $this->manager->getAvailableExtensions()->filterByActivated();
        $finder     = new Finder();
        $collection = collect();
        foreach ($extensions as $extension) {
            $path = $extension->getPath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Notifications';
            if (!is_dir($path)) {
                continue;
            }
            $collection->push($finder->files()->in($path)->name('*.php'));
        }
        return $collection;
    }

}
