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
declare(strict_types = 1);

namespace Antares\Notifications\Repository;

use Antares\Notifications\Model\NotificationsStackRead;
use Antares\Notifications\Model\NotificationSeverity;
use Antares\Foundation\Repository\AbstractRepository;
use Antares\Notifications\Model\NotificationCategory;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Notifications\Model\Notifications;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Antares\Logger\Model\Logs;
use Exception;

class StackRepository extends AbstractRepository
{

    /**
     * name of repositroy model
     *
     * @return NotificationsStack
     */
    public function model()
    {
        return NotificationsStack::class;
    }

    /**
     * Gets notifications severity ids
     * 
     * @return array
     */
    protected function getNotificationsSeverityIds()
    {
        return NotificationSeverity::query()->whereIn('name', config('antares/notifications::notification_severity'))->get()->pluck('id')->toArray();
    }

    /**
     * Gets alerts severity ids
     * 
     * @return array
     */
    protected function getAlertsSeverityIds()
    {
        return NotificationSeverity::query()->whereIn('name', config('antares/notifications::alert_severity'))->get()->pluck('id')->toArray();
    }

    /**
     * Gets base query builder for notifications and alerts
     * 
     * @return Builder
     */
    public function query()
    {
        $read = NotificationsStackRead::query()
            ->select(['stack_id'])
            ->withTrashed()
            ->where('user_id', user()->id)
            ->whereNotNull('deleted_at')
            ->pluck('stack_id');

        return NotificationsStack::query()
            ->distinct()
            ->select(['tbl_notifications_stack.*'])
            ->whereHas('contents', function(Builder $query) {
                $query->where(['lang_id' => lang_id()]);
            })
            ->where(function (Builder $query) {
                $query
                    ->whereNull('author_id')
                    ->orWhere('author_id', user()->id)
                    ->orWhereHas('author', function(Builder $q) {
                        $q->whereHas('roles', function(Builder $rolesQuery) {
                            $rolesQuery->whereIn('tbl_roles.id', user()->roles->first()->getChilds());
                        });
                    })
                    ->orWhereHas('params', function(Builder $q) {
                        $q->where('model_id', user()->id);
                    });
            })
            ->whereNotIn('id', $read)
            ->with('author')
            ->with('contents')
            ->with('notification.severity')
            ->orderBy('tbl_notifications_stack.created_at', 'desc');
    }

    /**
     * Gets user notifications
     *
     * @return Builder
     */
    public function getNotifications()
    {
        return $this->query()->whereHas('notification', function(Builder $query) {
            $query->whereIn('tbl_notifications.severity_id', $this->getNotificationsSeverityIds());
        });
    }

    /**
     * Alerts getter
     * 
     * @return Builder
     */
    public function getAlerts()
    {
        return $this->query()->whereHas('notification', function(Builder $query) {
            $query->whereIn('tbl_notifications.severity_id', $this->getAlertsSeverityIds());
        });
    }

    /**
     * Gets notifications and alerts count
     * 
     * @return array
     */
    public function getCount()
    {
        $read = $this->getReadStack();

        return [
            'notifications' => $this->getNotifications()->whereNotIn('id', $read)->count(),
            'alerts'        => $this->getAlerts()->whereNotIn('id', $read)->count(),
        ];
    }

    /**
     * @return mixed
     */
    protected function getReadStack() {
        return NotificationsStackRead::query()->select(['stack_id'])->withTrashed()->where('user_id', user()->id)->pluck('stack_id');
    }

    /**
     * Deletes all messages
     * 
     * @param String $type
     * @return boolean
     */
    public function clear($type = 'notifications')
    {
        $builder = ($type == 'alerts') ? $this->getAlerts() : $this->getNotifications();

        return NotificationsStackRead::query()->whereIn('stack_id', $builder->pluck('id'))->delete();
    }

    /**
     * Mark notifications or alerts as read
     * 
     * @param String $type
     * @return boolean
     */
    public function markAsRead($type = 'notifications')
    {
        DB::beginTransaction();
        try {
            $builder = ($type == 'alerts') ? $this->getAlerts() : $this->getNotifications();
            $read    = $this->getReadStack();
            $items   = $builder->whereNotIn('id', $read)->get();

            foreach ($items as $item) {
                $item->read()->save(new NotificationsStackRead([
                    'user_id' => user()->id
                ]));
                $item->save();
            }
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }

        DB::commit();

        return true;
    }

    /**
     * Deletes item by id
     * 
     * @param mixed $id
     * @return boolean
     */
    public function deleteById($id)
    {
        $read = NotificationsStackRead::query()->where([
                    'stack_id' => $id,
                    'user_id'  => user()->id
                ])->first();

        if (!is_null($read)) {
            return $read->delete();
        }

        return false;
    }

    /**
     * Saves notification
     * 
     * @param array $log
     * @param String $message
     * @return boolean
     * @throws Exception
     */
    public function save(array $log, $message = null)
    {
        DB::beginTransaction();
        try {
            $this->resolveJsonCastableColumns($log);
            $inserted = Logs::query()->insert($log);

            if (!$inserted) {
                throw new Exception('Unable to save log');
            }

            /* @var $notification Notifications */
            $notification = Notifications::query()->firstOrNew([
                'brand_id'    => brand_id(),
                'category_id' => NotificationCategory::query()->where('name', 'default')->first()->id,
                'type_id'     => NotificationTypes::query()->where('name', 'notification')->first()->id,
                'name'        => $log['name'],
            ]);

            if (!$notification->exists) {
                $notification->active = 1;
                $notification->save();
                $notification->contents()->save(new NotificationContents([
                    'lang_id' => lang_id(),
                    'title'   => $log['name'],
                    'content' => $message
                ]));
            }
        } catch (Exception $ex) {
            DB::rollBack();
            return false;
        }

        DB::commit();
        return true;
    }

    /**
     * Resolves json castable columns
     *
     * @param array $log
     * @return array
     */
    protected function resolveJsonCastableColumns(array &$log)
    {
        foreach ($log as $name => $value) {
            if (!is_array($value)) {
                continue;
            }
            $log[$name] = json_encode($value);
        }
        return $log;
    }

    /**
     * Fetch stacks
     *
     * @return Builder
     */
    public function fetchAll(): Builder
    {
        return NotificationsStack::query()
            ->select([
                'tbl_notifications_stack.id',
                'tbl_notifications_stack.variables',
                'tbl_notifications_stack.created_at',
                'tbl_notifications_stack.author_id',
                'tbl_notifications.event as event',
                'tbl_notifications.name',
                'tbl_notification_types.title as type',
                'tbl_languages.code as lang_code',
                'tbl_languages.name as lang_name',
                'tbl_roles.area as area',
                DB::raw('CONCAT_WS(" ", tbl_users.firstname, tbl_users.lastname) AS fullname')
            ])
            ->leftJoin('tbl_notification_contents', 'tbl_notifications_stack.notification_id', '=', 'tbl_notification_contents.notification_id')
            ->leftJoin('tbl_notifications', 'tbl_notifications_stack.notification_id', '=', 'tbl_notifications.id')
            ->leftJoin('tbl_notification_types', 'tbl_notifications.type_id', '=', 'tbl_notification_types.id')
            ->leftJoin('tbl_languages', 'tbl_notification_contents.lang_id', '=', 'tbl_languages.id')
            ->leftJoin('tbl_users', 'tbl_notifications_stack.author_id', '=', 'tbl_users.id')
            ->leftJoin('tbl_user_role', 'tbl_users.id', '=', 'tbl_user_role.user_id')
            ->leftJoin('tbl_roles', 'tbl_user_role.role_id', '=', 'tbl_roles.id')
            ->groupBy('tbl_notifications_stack.id')
            ->where(function(Builder $query) {
                $query
                    ->whereNull('author_id')
                    ->orWhere('author_id', user()->id)
                    ->orWhereIn('tbl_roles.id', user()->roles->first()->getChilds());
            });
    }

    /**
     * Fetch one stack item
     * 
     * @param int $id
     * @return Builder
     */
    public function fetchOne(int $id): Builder
    {
        return NotificationsStack::query()
            ->withoutGlobalScopes()
            ->with('notification.contents.lang', 'notification.type', 'author.roles')
            ->where('id', $id)
            ->where(function(Builder $q) {
                $q->whereNull('author_id')->orWhere('author_id', user()->id)->orWhereHas('author', function(Builder $q) {
                    $q->whereHas('roles', function(Builder $q) {
                        $q->whereIn('tbl_roles.id', user()->roles->first()->getChilds());
                    });
                });
            });
    }

}
