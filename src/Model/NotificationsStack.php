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

namespace Antares\Notifications\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Antares\Model\User;

/**
 * Class NotificationsStack
 * @package Antares\Notifications\Model
 *
 * @property integer $id
 * @property string $title
 * @property integer $author_id
 * @property integer $type_id
 * @property integer $severity_id
 * @property string $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property User $author
 * @property NotificationSeverity $severity
 * @property NotificationTypes $type
 * @property-read Collection|NotificationsStackParams[] $params
 * @property-read Collection|NotificationsStackRead[] $read
 */
class NotificationsStack extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notifications_stack';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title', 'type_id', 'severity_id', 'content', 'author_id', 'created_at', 'updated_at'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Relation to stack params table
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function params()
    {
        return $this->hasMany(NotificationsStackParams::class, 'stack_id', 'id');
    }

    /**
     * Relation to stack read table
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function read()
    {
        return $this->hasMany(NotificationsStackRead::class, 'stack_id', 'id');
    }

    /**
     * Returns author.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function author()
    {
        return $this->hasOne(User::class, 'id', 'author_id');
    }

    /**
     * Returns severity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function severity()
    {
        return $this->hasOne(NotificationSeverity::class, 'id', 'severity_id');
    }

    /**
     * Returns type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne(NotificationTypes::class, 'id', 'type_id');
    }

}
