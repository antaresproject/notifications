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

namespace Antares\Notifications\Processor;

use Antares\Notifications\Contracts\IndexPresenter as Presenter;
use Antares\Notifications\Decorator\MailDecorator;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\PreviewNotification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Antares\Notifications\Adapter\VariablesAdapter;
use Antares\Notifications\Contracts\IndexListener;
use Antares\Notifications\Repository\Repository;
use Antares\Foundation\Processor\Processor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;
use Antares\Notifications\Facade\Notification;

class IndexProcessor extends Processor
{

    /**
     * instance of variables adapter
     *
     * @var VariablesAdapter
     */
    protected $variablesAdapter;

    /**
     * repository instance
     *
     * @var Repository
     */
    protected $repository;

    /**
     * constructing
     * 
     * @param Presenter $presenter
     * @param VariablesAdapter $adapter
     * @param Repository $repository
     */
    public function __construct(Presenter $presenter, VariablesAdapter $adapter, Repository $repository)
    {
        $this->presenter        = $presenter;
        $this->variablesAdapter = $adapter;
        $this->repository       = $repository;
    }

    /**
     * default index action
     * 
     * @return String $type
     */
    public function index($type = null)
    {
        return $this->presenter->table($type);
    }

    /**
     * shows edit form
     * 
     * @param mixed $id
     * @param String $locale
     * @param IndexListener $listener
     * @return View
     */
    public function edit($id, $locale, IndexListener $listener)
    {
        app('antares.asset')->container('antares/foundation::application')
            ->add('ckeditor', 'https://cdn.ckeditor.com/4.6.2/full-all/ckeditor.js', ['app_cache']);

        //app('antares.asset')->container('antares/foundation::application')->add('ckeditor', '/public/ckeditor/ckeditor.js', ['webpack_forms_basic']);

        $model = $this->repository->findByLocale($id, $locale);

        if (is_null($model)) {
            throw new ModelNotFoundException('Model not found');
        }
        return $this->presenter->edit($model, $locale);
    }

    /**
     * updates notification notification
     * 
     * @param IndexListener $listener
     * @return RedirectResponse
     */
    public function update(IndexListener $listener)
    {
        $id    = Input::get('id');
        $model = $this->repository->find($id);
        $form  = $this->presenter->getForm($model);
        if (!$form->isValid()) {
            return $listener->updateValidationFailed($id, $form->getMessageBag());
        }
        try {
            $this->repository->updateNotification($id, Input::all());
        } catch (Exception $ex) {
            return $listener->updateFailed();
        }

        return $listener->updateSuccess();
    }

    /**
     * sends test notification
     * 
     * @param IndexListener $listener
     * @param mixed $id
     * @return JsonResponse
     */
    public function sendTest(IndexListener $listener, $id = null)
    {
        $notifier   = null;
        $content    = null;
        $type       = null;

        if (request()->isMethod('post')) {
            $this->variablesAdapter->setPreviewMode(true);

            $inputs     = Input::all();
            $type       = $inputs['type'];
            $content    = new NotificationContents($inputs);

        } else {
            $model      = $this->repository->find($id);
            $type       = $model->type->name;
            $content    = $model->contents->first();
        }

        try {
            Notification::send(auth()->user(), new PreviewNotification($type, $content));

            if (request()->ajax()) {
                return new JsonResponse(trans('Message has been sent'), 200);
            }

            return $listener->sendSuccess();
        }
        catch(Exception $e) {
            if (request()->ajax()) {
                return new JsonResponse($e->getMessage(), 500);
            }

            return $listener->sendFailed();
        }
    }

    /**
     * preview notification notification
     * 
     * @return View
     */
    public function preview(array $data)
    {
        $this->variablesAdapter->setPreviewMode(true);

        $data['content'] = $this->variablesAdapter->get(  Arr::get($data, 'content', '') );

        if( Arr::get($data, 'type') === 'email') {
            $data['content'] = MailDecorator::decorate($data['content']);
        }

        return $this->presenter->preview($data);
    }

    /**
     * change notification notification status
     * 
     * @param IndexListener $listener
     * @param mixed $id
     * @return RedirectResponse
     */
    public function changeStatus(IndexListener $listener, $id)
    {
        $model = $this->repository->find($id);
        if (is_null($model)) {
            return $listener->changeStatusFailed();
        }
        $model->active = ($model->active) ? 0 : 1;
        $model->save();
        return $listener->changeStatusSuccess();
    }

    /**
     * Create notification notification form
     * 
     * @param String $type
     * @return View
     */
    public function create($type = null)
    {
        app('antares.asset')->container('antares/foundation::application')
            ->add('ckeditor', 'https://cdn.ckeditor.com/4.6.2/full-all/ckeditor.js', ['app_cache']);

        //app('antares.asset')->container('antares/foundation::application')->add('ckeditor', '/ckeditor/ckeditor.js', ['webpack_forms_basic']);

        return $this->presenter->create($this->repository->makeModel()->getModel(), $type);
    }

    /**
     * store new notification notification
     * 
     * @param IndexListener $listener
     * @return RedirectResponse
     */
    public function store(IndexListener $listener)
    {
        $model = $this->repository->makeModel()->getModel();
        $form  = $this->presenter->getForm($model)->onCreate();
        if (!$form->isValid()) {
            return $listener->storeValidationFailed($form->getMessageBag());
        }
        try {
            $this->repository->store(Input::all());
        } catch (Exception $ex) {
            return $listener->createFailed();
        }
        return $listener->createSuccess();
    }

    /**
     * deletes custom notification
     * 
     * @param mixed $id
     * @param IndexListener $listener
     * @return RedirectResponse
     */
    public function delete($id, IndexListener $listener)
    {
        try {
            $model = $this->repository->makeModel()->findOrFail($id);
            $model->delete();
            return $listener->deleteSuccess();
        } catch (Exception $ex) {
            return $listener->deleteFailed();
        }
    }

}
