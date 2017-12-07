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

namespace Antares\Notifications\Decorator;

use Antares\Notifications\Model\NotificationsStack;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class SidebarItemDecorator
{

    /**
     * Decorates notifications of alerts
     * 
     * @param Collection|NotificationsStack[] $items
     * @param String $type
     * @return array
     * @throws RuntimeException
     */
    public function decorate(Collection $items, $type = 'notification')
    {
        $view = config('antares/notifications::templates.' . $type);
        if (is_null($view)) {
            throw new RuntimeException('Unable to resolve notification partial view.');
        }
        $return = [];
        foreach ($items as $item) {
            array_push($return, $this->item($item, $view));
        }
        return $return;
    }

    /**
     * Decorates single item.
     *
     * @param NotificationsStack $item
     * @param string $view
     * @return string
     */
    public function item(NotificationsStack $item, string $view)
    {
        $params  = [
            'id'         => $item->id,
            'author'     => $item->author,
            'title'      => $item->title,
            'value'      => $item->content,
            'priority'   => priority_label($item->severity->name),
            'created_at' => $item->created_at
        ];
        $request = request();
        if (!$request->ajax() && $request->isJson() && $request->wantsJson()) {
            $params['priority'] = $item->severity;
            return $params;
        }
        return view($view, $params)->render();
    }

}
