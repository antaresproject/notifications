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

use Antares\Translations\Models\Languages;
use Antares\Model\Eloquent;

/**
 * Class NotificationContents
 * @package Antares\Notifications\Model
 *
 * @property integer $id
 * @property integer $notification_id
 * @property integer $lang_id
 * @property string $title
 * @property string $content
 * @property Notifications $notification
 * @property Languages $lang
 */
class NotificationContents extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'tbl_notification_contents';

    /**
     * The class name to be used in polymorphic relations.
     *
     * @var string
     */
    protected $morphClass = 'TemplateContents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['notification_id', 'lang_id', 'title', 'content'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * {@inheritdoc}
     */
    protected $with = ['lang'];

    /**
     * {@inheritdoc}
     */
    protected $attributes = [
        'title'         => '',
        'content'       => '',
    ];

    /**
     * notification belongs to relation
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function notification()
    {
        return $this->belongsTo(Notifications::class, 'notification_id', 'id');
    }

    /**
     * Relation to languages table
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lang()
    {
        return $this->hasOne(Languages::class, 'id', 'lang_id');
    }

    /**
     * fires events for notification template
     * 
     * @return array
     */
    protected function fires()
    {
        $className = isset($this->notification->classname) ? snake_case(class_basename($this->notification->classname)) : false;

        if (!$className) {
            return [];
        }

        $before = event('notifications:' . $className . '.render.before');
        $after  = event('notifications:' . $className . '.render.after');

        return [
            'before' => !empty($before) ? current($before) : '',
            'after'  => !empty($after) ? current($after) : ''
        ];
    }

    /**
     * magic __get overwritten to apply template event fireing
     * 
     * @param mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        $return = parent::__get($key);
        if ($key !== 'content') {
            return $return;
        }
        $fired = $this->fires();

        if(count($fired)) {
            return $fired['before'] . $return . $fired['after'];
        }

        return $return;
    }

    /**
     * saves notification content without data from fired events
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = array())
    {
        $fired = $this->fires();

        if( count($fired) ) {
            $content = str_replace([$fired['before'], $fired['after']], '', $this->content);

            $this->setAttribute('content', $content);
        }

       return parent::save($options);
    }

}
