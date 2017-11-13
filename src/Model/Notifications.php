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

namespace Antares\Notifications\Model;

use Antares\Notifications\Services\EventsRegistrarService;
use Antares\Translations\Models\Languages;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Antares\Model\Eloquent;
use Illuminate\Support\Arr;

/**
 * Class Notifications
 * @package Antares\Notifications\Model
 *
 * @property integer $id
 * @property integer $severity_id
 * @property string $event
 * @property NotifiableEvent|string|null $event_model
 * @property string $event_label
 * @property integer $type_id
 * @property boolean $active
 * @property string $checksum
 * @property string $name
 * @property string $source
 * @property string $category
 * @property array $recipients
 * @method static Builder|Notifications active()
 * @property NotificationTypes $type
 * @property NotificationSeverity $severity
 * @property-read Collection|NotificationContents[] $contents
 * @property-read Collection|NotificationsStack[] $stack
 *
 */
class Notifications extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'source',
        'severity_id',
        'type_id',
        'event',
        'active',
        'name',
        'checksum',
        'recipients',
        'category',
    ];

    /**
     * {@inheritdoc}
     */
    protected $attributes = [
        'severity_id'   => 3 //medium
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'id'            => 'integer',
        'severity_id'   => 'integer',
        'type_id'       => 'integer',
        'active'        => 'boolean',
        'recipients'    => 'json',
    ];

    /**
     * {@inheritdoc}
     */
    protected $appends = [
        'event_label',
        'event_model',
    ];

    /**
     * Query scope for active notifications.
     *
     * @param Builder $query
     */
    public function scopeActive(Builder $query)
    {
        $query->where('active', 1);
    }

    /**
     * relation to template types
     * 
     * @return HasOne
     */
    public function type()
    {
        return $this->hasOne(NotificationTypes::class, 'id', 'type_id');
    }

    /**
     * relation to template contents
     * 
     * @return HasMany
     */
    public function contents()
    {
        return $this->hasMany(NotificationContents::class, 'notification_id', 'id');
    }

    /**
     * Relation to notifications stack
     * 
     * @return HasMany
     */
    public function stack()
    {
        return $this->hasMany(NotificationsStack::class, 'notification_id', 'id');
    }

    /**
     * Relation to notification severity
     * 
     * @return HasOne
     */
    public function severity()
    {
        return $this->hasOne(NotificationSeverity::class, 'id', 'severity_id');
    }

    /**
     * Gets patterned url for search engines
     * 
     * @return String
     */
    public static function getPatternUrl()
    {
        return handles('antares::notifications/edit/{id}');
    }

    /**
     * @param Languages $language
     * @return NotificationContents
     */
    public function lang(Languages $language) {
        /* @var $content \Antares\Notifications\Model\NotificationContents */

        if( ! $this->relationLoaded('contents')) {
            $this->with('contents');
        }

        foreach($this->contents as $content) {
            if($language->code === $content->lang->code) {
                return $content;
            }
        }

        return new NotificationContents([
            'lang_id' => $language->id,
        ]);
    }

    /**
     * @return NotifiableEvent|string|null
     */
    public function getEventModelAttribute() {
        $event  = (string) Arr::get($this->attributes, 'event');
        $object = $this->getNotifiableEvent($event);

        return $object ?: $event;
    }

    /**
     * @return string
     */
    public function getEventLabelAttribute() {
        $event  = (string) Arr::get($this->attributes, 'event');
        $object = $this->getNotifiableEvent($event);

        return $object ? $object->getLabel() : $event;
    }

    /**
     * @param string|null $event
     * @return NotifiableEvent|null
     */
    private function getNotifiableEvent(string $event = null) {
        return $event ? $this->getEventsRegistrarService()->getByClassName($event) : null;
    }

    /**
     * @return EventsRegistrarService
     */
    private function getEventsRegistrarService() : EventsRegistrarService {
        return app()->make(EventsRegistrarService::class);
    }

}
