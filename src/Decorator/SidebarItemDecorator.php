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

namespace Antares\Notifications\Decorator;

use Antares\Notifications\Adapter\VariablesAdapter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class SidebarItemDecorator
{

    /**
     * Decorates notifications of alerts
     * 
     * @param Collection $items
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
            $return[] = $this->item($item, $view);
        }
        return $return;
    }

    /**
     * Decorates single item
     * 
     * @param Model $item
     * @param String $view
     * @return String
     */
    public function item(Model $item, $view)
    {
        $content = $this->getVariablesAdapter()->get($item->content[0]->content, (array) $item->variables);

        return view($view, [
            'id'         => $item->id,
            'author'     => $item->author,
            'title'      => $item->content[0]->title,
            'value'      => $content,
            'priority'   => priority_label($item->notification->severity->name),
            'created_at' => $item->created_at
        ])->render();
    }

    /**
     * @return VariablesAdapter
     */
    protected function getVariablesAdapter() {
        return app(VariablesAdapter::class);
    }

}
