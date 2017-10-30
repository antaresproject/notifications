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
declare(strict_types = 1);

namespace Antares\Notifications\Processor;

use Antares\Modules\BillevioBase\Helpers\ResponseHelper;
use Antares\Notifications\Decorator\MailDecorator;
use Antares\Notifications\Decorator\SidebarItemDecorator;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Repository\StackRepository;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Foundation\Processor\Processor;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Exception;
use Log;
use DB;

class LogsProcessor extends Processor {

    /**
     * NotificationsStack instance.
     *
     * @var NotificationsStack
     */
    protected $stack;

    /**
     * Stack Repository instance.
     *
     * @var StackRepository
     */
    protected $stackRepository;

    /**
     * Sidebar item decorator instance.
     *
     * @var SidebarItemDecorator
     */
    protected $sidebarItemDecorator;

    /**
     * Content parser instance.
     *
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * LogsProcessor constructor.
     * @param NotificationsStack $stack
     * @param StackRepository $stackRepository
     * @param SidebarItemDecorator $sidebarItemDecorator
     * @param ContentParser $contentParser
     */
    public function __construct(NotificationsStack $stack, StackRepository $stackRepository, SidebarItemDecorator $sidebarItemDecorator, ContentParser $contentParser)
    {
        $this->stack = $stack;
        $this->stackRepository = $stackRepository;
        $this->sidebarItemDecorator = $sidebarItemDecorator;
        $this->contentParser = $contentParser;
    }

    /**
     * Preview notification log
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|JsonResponse|View
     */
    public function preview($id)
    {
        /* @var $item NotificationsStack */
        $item       = $this->stackRepository->fetchOne((int) $id)->firstOrFail();
        $typeName   = $item->notification->type->name;

        if ( in_array($typeName, ['mail', 'sms'], true) ) {
            $title      = $this->contentParser->parse($item->contents[0]->title, $item->variables );
            $content    = $this->contentParser->parse($item->contents[0]->content , $item->variables);

            if($typeName === 'mail') {
                $content = MailDecorator::decorate($content);
            }

            return view('antares/notifications::admin.logs.preview', compact('title', 'content'));
        }

        $content = $this->sidebarItemDecorator->item($item, config('antares/notifications::templates.notification'));

        return new JsonResponse(compact('content'), 200);
    }

    /**
     * Deletes notification log
     *
     * @param array $ids
     * @return ResponseHelper
     */
    public function delete(array $ids): ResponseHelper {
        $url = handles('antares::notifications/logs');

        if( count($ids) === 0) {
            $message = trans('antares/notifications::logs.notification_delete_failed');

            return ResponseHelper::error($message, $url);
        }

        try {
            $stacks = NotificationsStack::query()->whereIn('id', $ids)->get();

            foreach($stacks as $stack) {
                $stack->delete();
            }

            $message    = trans('antares/notifications::logs.notification_delete_success');
            $response   = ResponseHelper::success($message, $url);

            DB::commit();
        }
        catch(Exception $e) {
            Log::emergency($e->getMessage());
            DB::rollBack();

            $message    = trans('antares/notifications::logs.notification_delete_failed');
            $response   = ResponseHelper::error($message, $url);
        }

        return $response;
    }

}
