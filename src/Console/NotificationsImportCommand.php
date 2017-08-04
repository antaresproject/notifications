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
use Illuminate\Notifications\Notification;
use Symfony\Component\Finder\Finder;
use Antares\Foundation\Application;
use Antares\Extension\Manager;
use Exception;

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
     * Application instance
     *
     * @var Application
     */
    protected $app;

    /**
     * Construct
     * 
     * @param Manager $manager
     * @param Application $app
     */
    public function __construct(Manager $manager, Application $app)
    {
        parent::__construct();
        $this->manager = $manager;
        $this->app     = $app;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $files = $this->getFiles();
        foreach ($files as $file) {
            try {
                $message = $this->app->make($file);
            } catch (Exception $ex) {
                continue;
            }
            if (!isset($message->templates)) {
                continue;
            }
            foreach ($message->templates as $lang => $templates) {
                if (!isset($templates['subject'])) {
                    continue;
                }
            }
        }
    }

    /**
     * Gets message files in application filesystem
     * 
     * @return \Illuminate\Support\Collection
     */
    protected function getFiles()
    {
        $autoload = require_once base_path('vendor/composer/autoload_classmap.php');
        $dirs     = $this->getDirs();
        return $dirs->flatMap(function($finder, $key) use($autoload) {
                    $files = [];
                    foreach ($finder as $file) {
                        if (!($key = array_search($file->getRealPath(), $autoload))) {
                            continue;
                        }
                        if (!class_exists($key)) {
                            continue;
                        }
                        array_push($files, $key);
                    }
                    return $files;
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

        $collection = collect();
        foreach ($extensions as $extension) {
            $path = $extension->getPath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Notifications';
            if (!is_dir($path)) {
                continue;
            }
            $finder = new Finder();
            $collection->push($finder->files()->in($path)->name('*.php'));
        }
        return $collection;
    }

}
