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

use Illuminate\Database\Eloquent\Model;

/**
 * Class NotificationsStackParams
 * @package Antares\Notifications\Model
 *
 * @property int $id
 * @property int $stack_id
 * @property int $model_id
 *
 * @property-read NotificationsStack $stack
 */
class NotificationsStackParams extends Model
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notifications_stack_params';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['stack_id', 'model_id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Relation to notifications table
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function stack()
    {
        return $this->hasOne(NotificationsStack::class, 'id', 'stack_id');
    }

}
