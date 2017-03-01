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
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */
declare(strict_types = 1);

namespace Antares\Notifications\Processor;

use Antares\Notifications\Http\Datatables\Logs as Datatables;
use Antares\Notifications\Decorator\SidebarItemDecorator;
use Antares\Notifications\Repository\StackRepository;
use Antares\Notifications\Http\Presenters\Breadcrumb;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Foundation\Template\EmailNotification;
use Antares\Notifications\Contracts\LogsListener;
use Antares\Foundation\Template\SmsNotification;
use Antares\Foundation\Processor\Processor;
use Illuminate\Http\RedirectResponse;
use Antares\Html\Form\FormBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Fluent;
use Illuminate\View\View;

class LogsProcessor extends Processor
{

    /**
     * breadcrumbs instance
     *
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * Datatables instance
     *
     * @var Datatables
     */
    protected $datatables;

    /**
     * NotificationsStack instance
     *
     * @var NotificationsStack
     */
    protected $stack;

    /**
     * Construct
     * 
     * @param Breadcrumb $breadcrumb
     * @param Datatables $datatables
     * @param NotificationsStack $stack
     */
    public function __construct(Breadcrumb $breadcrumb, Datatables $datatables, NotificationsStack $stack)
    {
        $this->breadcrumb = $breadcrumb;
        $this->datatables = $datatables;
        $this->stack      = $stack;
    }

    /**
     * Default index action
     * 
     * @return View
     */
    public function index(): View
    {
        $this->breadcrumb->onLogsList();
        return $this->datatables->render('antares/notifications::admin.logs.index');
    }

    /**
     * Preview notification log
     * 
     * @param mixed $id
     * @return View
     */
    public function preview($id)
    {
        $item = app(StackRepository::class)->fetchOne((int) $id)->firstOrFail();

        if (in_array($item->notification->type->name, ['email', 'sms'])) {
            $classname    = $item->notification->type->name === 'email' ? EmailNotification::class : SmsNotification::class;
            $notification = app($classname);
            $notification->setModel($item);
            return view('antares/notifications::admin.logs.preview', ['content' => $notification->render()]);
        }

        $decorator = app(SidebarItemDecorator::class);
        $decorated = $decorator->item($item, config('antares/notifications::templates.notification'));
        return new JsonResponse(['content' => $decorated], 200);
    }

    /**
     * Deletes notification log
     * 
     * @param LogsListener $listener
     * @param mixed $id
     * @return RedirectResponse
     */
    public function delete(LogsListener $listener, $id = null): RedirectResponse
    {
        $stack = !empty($ids   = input('attr')) ? $this->stack->newQuery()->whereIn('id', $ids) : $this->stack->newQuery()->findOrFail($id);
        if ($stack->delete()) {
            return $listener->deleteSuccess();
        }
        return $listener->deleteFailed();
    }

    /**
     * Saves notifications config
     * 
     * @param LogsListener $listener
     * @return RedirectResponse
     */
    public function config(LogsListener $listener)
    {
        $this->breadcrumb->onNotificationsConfig();
        $memory = app('antares.memory')->make('primary');
        $model  = new Fluent([
            'days' => $memory->get('notifications_remove_after_days', ''),
        ]);
        $form   = $this->form($model);
        if (!request()->isMethod('post')) {
            return view('antares/notifications::admin.logs.config', compact('form'));
        }
        if (!$form->isValid()) {
            return $listener->configValidationFailed($form->getMessageBag());
        }
        $memory->put('notifications_remove_after_days', input('days'));
        $memory->finish();
        return $listener->configSaveSuccess();
    }

    /**
     * Notification configuration form creator
     * 
     * @param Fluent $model
     * @return FormBuilder
     */
    protected function form(Fluent $model): FormBuilder
    {
        return new FormBuilder(new \Antares\Notifications\Http\Form\Configuration($model));
    }

}
