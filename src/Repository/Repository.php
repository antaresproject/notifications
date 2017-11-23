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

namespace Antares\Notifications\Repository;

use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\NotificationTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Repository {

    /**
     * Gets notification contents
     *
     * @param null $type
     * @return \Illuminate\Database\Eloquent\Collection|NotificationContents[]
     */
    public function getNotificationContents($type = null)
    {
        return NotificationContents::query()
            ->with('notification')
            ->whereHas('lang', function(Builder $query) {
                $query->where('code', locale());
            })
            ->whereHas('notification', function(Builder $query) use($type) {
                if (is_null($type)) {
                    $query->whereHas('type', function(Builder $q) {
                        $q->whereIn('name', ['sms', 'mail']);
                    });
                }
                elseif (is_numeric($type)) {
                    $query->where('type_id', $type);
                }
                elseif (is_string($type)) {
                    $query->whereHas('type', function(Builder $q) use($type) {
                        $q->where('name', $type);
                    });
                }
            })->get();
    }

    /**
     * Gets notification contents for events
     *
     * @param array $events
     * @param null $type
     * @return \Illuminate\Database\Eloquent\Collection|NotificationContents[]
     */
    public function getNotificationContentsByEvents(array $events, $type = null)
    {
        return NotificationContents::query()
            ->with('notification')
            ->whereHas('lang', function(Builder $query) {
                $query->where('code', locale());
            })
            ->whereHas('notification', function(Builder $query) use($events, $type) {
                $query->whereIn('event', $events);

                if (is_null($type)) {
                    $query->whereHas('type', function(Builder $q) {
                        $q->whereIn('name', ['sms', 'mail']);
                    });
                }
                elseif (is_numeric($type)) {
                    $query->where('type_id', $type);
                }
                elseif (is_string($type)) {
                    $query->whereHas('type', function(Builder $q) use($type) {
                        $q->where('name', $type);
                    });
                }
            })->get();
    }

    /**
     * Gets decorated notification types
     *
     * @return Collection
     */
    public function getDecoratedNotificationTypes()
    {
        return NotificationTypes::query()->whereIn('name', ['mail', 'sms'])->pluck('name', 'id')->map(function ($item) {
            return ucfirst($item);
        });
    }

}
