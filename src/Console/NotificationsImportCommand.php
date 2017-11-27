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

use Antares\Extension\Contracts\ExtensionContract;
use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Synchronizer;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Antares\Foundation\Application;
use Antares\Extension\Manager;
use Symfony\Component\Finder\SplFileInfo;
use ReflectionClass;

class NotificationsImportCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'notifications:import {extension? : Extension full name} {--force}';

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
     * Synchronizer instance
     *
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * Extension instance.
     *
     * @var ExtensionContract|null
     */
    private $extension;

    /**
     * NotificationsImportCommand constructor.
     * @param Manager $manager
     * @param Application $app
     * @param Synchronizer $synchronizer
     */
    public function __construct(Manager $manager, Application $app, Synchronizer $synchronizer)
    {
        parent::__construct();

        $this->manager      = $manager;
        $this->app          = $app;
        $this->synchronizer = $synchronizer;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $forceMode     = $this->option('force');
        $extensionName = $this->argument('extension');

        $this->synchronizer->setForceMode($forceMode);

        if ($extensionName) {
            $extension = $this->manager->getAvailableExtensions()->findByName($extensionName);

            if ($extension instanceof ExtensionContract) {
                $this->extension = $extension;
            }
            else {
                $this->error('Extension not found.');
                return;
            }
        }

        $files = $this->getFiles();

        /* @var $file string */
        foreach ($files->all() as $file) {
            $reflection = new ReflectionClass($file);

            if ($this->hasDesiredInterface($reflection)) {
                $templates = call_user_func(array($file, 'templates'));

                $this->synchronizer->syncTemplates($file, $templates);
            }
        }
    }

    /**
     * Determines if the given class reflection has desired interface.
     *
     * @param ReflectionClass $class
     * @return bool
     */
    protected function hasDesiredInterface(ReflectionClass $class): bool
    {
        return !$class->isAbstract() && in_array(NotificationEditable::class, $class->getInterfaceNames(), true);
    }

    /**
     * Gets message files in application filesystem.
     * 
     * @return Collection|string[]
     * @throws \Exception
     */
    protected function getFiles()
    {
        $path       = base_path('vendor/composer/autoload_classmap.php');
        $autoload   = require_once $path;
        $dirs       = $this->extension ? $this->getExtensionDirs($this->extension) : $this->getExtensionsDirs();

        if( ! is_array($autoload)) {
            throw new \Exception('The content of [' . $path . '] is not array');
        }

        if (!$dirs) {
            return new Collection();
        }

        if ($dirs instanceof Finder) {
            $dirs = new Collection([$dirs]);
        }

        /* @var $file SplFileInfo */

        return $dirs->flatMap(function(Finder $finder) use($autoload) {
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
     * Gets directories with notifications.
     * 
     * @return Collection|Finder[]
     */
    protected function getExtensionsDirs(): Collection
    {
        $extensions = $this->manager->getAvailableExtensions()->filterByActivated();
        $collection = collect();

        foreach ($extensions as $extension) {
            $finder = $this->getExtensionDirs($extension);

            if ($finder) {
                $collection->push($finder);
            }
        }

        return $collection;
    }

    /**
     * Returns notification path as Finder object for given extension. If path is not recognized NULL will be returned.
     *
     * @param ExtensionContract $extension
     * @return null|Finder
     */
    protected function getExtensionDirs(ExtensionContract $extension)
    {
        $path = $extension->getPath() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Notifications';

        if (is_dir($path)) {
            return Finder::create()->files()->in($path)->name('*.php');
        }

        return null;
    }

}
