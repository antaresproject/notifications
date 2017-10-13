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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Antares\Model\Eloquent;

/**
 * Class Notifications
 * @package Antares\Notifications\Model
 *
 * @property integer $id
 * @property integer $severity_id
 * @property integer $category_id
 * @property integer $type_id
 * @property boolean $active
 * @property string $classname
 * @property string $checksum
 * @property string $event
 * @method static Builder|Notifications active()
 * @property NotificationTypes $type
 * @property NotificationCategory $category
 * @property NotificationSeverity $severity
 * @property-read Collection|NotificationContents[] $contents
 * @property-read Collection|NotificationsStack[] $stack
 *
 */
class Notifications extends Eloquent
{

    /**
     * Low priority notification
     *
     * @var String 
     */
    protected $priority = 'low';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notifications';

    /**
     * The class name to be used in polymorphic relations.
     *
     * @var string
     */
    protected $morphClass = 'Notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['severity_id', 'category_id', 'type_id', 'active', 'classname', 'checksum'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Query scope for active jobs
     *
     * @param  object     $query
     *
     * @return void
     */
    public function scopeActive($query)
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
     * relation to notification categories
     * 
     * @return HasOne
     */
    public function category()
    {
        return $this->hasOne(NotificationCategory::class, 'id', 'category_id');
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

}
