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

use Antares\Model\Eloquent;

/**
 * Class NotificationSeverity
 * @package Antares\Notifications\Model
 *
 * @property int $id
 * @property int $notification_id
 * @property string $name
 *
 * @property-read Notifications $notification
 */
class NotificationSeverity extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notification_severity';

    /**
     * The class name to be used in polymorphic relations.
     *
     * @var string
     */
    protected $morphClass = 'NotificationSeverity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * template belongs to relation
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notification()
    {
        return $this->belongsTo(Notifications::class, 'severity_id', 'id');
    }

    /**
     * Gets medium notification severity
     * 
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeMedium($query)
    {
        $query->where('name', 'medium');
    }

}
