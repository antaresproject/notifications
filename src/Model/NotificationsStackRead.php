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

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Antares\Model\User;

/**
 * Class NotificationsStackRead
 * @package Antares\Notifications\Model
 *
 * @property integer $id
 * @property integer $stack_id
 * @property integer $user_id
 * @property Carbon $deleted_at
 * @property NotificationsStack $stack
 * @property User $user
 */
class NotificationsStackRead extends Model
{

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notifications_stack_read';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stack_id', 'user_id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Relation to notifications stack table
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function stack()
    {
        return $this->belongsTo(NotificationsStack::class, 'stack_id', 'id');
    }

    /**
     * Relation to user table
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
