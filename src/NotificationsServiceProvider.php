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

namespace Antares\Notifications;

use Antares\Notifications\Contracts\RendererContract;
use Antares\Notifications\Console\NotificationCategoriesCommand;
use Antares\Notifications\Console\NotificationSeveritiesCommand;
use Antares\Foundation\Support\Providers\ModuleServiceProvider;
use Antares\Notifications\Console\NotificationsImportCommand;
use Antares\Notifications\Console\NotificationTypesCommand;
use Antares\Notifications\Http\Middleware\TransformBoolean;
use Antares\Notifications\Listener\ConfigurationListener;
use Antares\Notifications\Listener\NotificationsListener;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Renderers\TwigRenderer;
use Antares\Notifications\Services\EventsRegistrarService;
use Antares\Notifications\Services\NotificationsService;
use Antares\Notifications\Services\VariablesService;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Antares\Notifications\Console\NotificationsRemover;
use Antares\Acl\Http\Handlers\ControlPane;
use Antares\Notifier\Mail\Mailer;
use Antares\Memory\Model\Option;
use Illuminate\Routing\Router;

class NotificationsServiceProvider extends ModuleServiceProvider
{

    /**
     * The application or extension namespace.
     *
     * @var string|null
     */
    protected $namespace = 'Antares\Notifications\Http\Controllers\Admin';

    /**
     * The application or extension group namespace.
     *
     * @var string|null
     */
    protected $routeGroup = 'antares/notifications';

    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'antares.form: foundation.settings' => ConfigurationListener::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->bindContracts();

        $this->app->singleton('notifications.contents', function () {
            return new Contents();
        });

        $this->commands([
            NotificationCategoriesCommand::class,
            NotificationSeveritiesCommand::class,
            NotificationTypesCommand::class,
            NotificationsRemover::class,
            NotificationsImportCommand::class
        ]);

        $this->app->singleton(ChannelManager::class, function ($app) {
            return new ChannelManager($app);
        });

        $this->app->singleton(Mailer::class, function () {
            return $this->app->make('antares.support.mail');
        });

        $this->app->singleton(VariablesService::class);
        $this->app->singleton(EventsRegistrarService::class);
        $this->app->singleton(NotificationsService::class);
        $this->app->singleton(NotificationsListener::class);
        $this->app->singleton(ContentParser::class);

        $this->app->bind(RendererContract::class, TwigRenderer::class);
    }

    public function boot() {
        parent::boot();

        $router = $this->app->make(Router::class);
        $router->pushMiddlewareToGroup('web', TransformBoolean::class);
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function bootExtensionComponents()
    {
        $path = __DIR__ . '/../';
        $this->addConfigComponent('antares/notifications', 'antares/notifications', "{$path}/resources/config");
        $this->addLanguageComponent('antares/notifications', 'antares/notifications', "{$path}/resources/lang");
        $this->addViewComponent('antares/notifications', 'antares/notifications', "{$path}/resources/views");
        $this->bootMemory();

        $this->app->make('view')->composer('antares/notifications::admin.logs.config', ControlPane::class);

        Option::observe(new ConfigurationListener());

        $this->app->alias(Mailer::class, MailerContract::class);

        app()->make(NotificationsListener::class)->boot();

        $this->importNotifications('antaresproject/component-notifications');
    }

    /**
     * Boot extension routing.
     *
     * @return void
     */
    protected function loadRoutes()
    {
        $path = __DIR__;
        $this->loadBackendRoutesFrom("{$path}/Http/backend.php");
        $this->loadFrontendRoutesFrom("{$path}/Http/frontend.php");
    }

    /**
     * Booting events
     */
    protected function bootMemory()
    {
        $this->app->make('antares.acl')->make($this->routeGroup)->attach(
            $this->app->make('antares.platform.memory')
        );
    }

}
