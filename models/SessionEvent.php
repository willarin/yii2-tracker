<?php
/**
 * Session event recording functions
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class SessionEvent
 * @package willarin\tracker\models
 */
class SessionEvent extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return 'SessionEvent';
    }
    
    /**
     * save event
     *
     * @param string $name
     * @param mixed $params
     * @param integer $sessionUrlId sessionUrlId identifier for the event page
     */
    public static function saveEvent($name, $params, $sessionUrlId = 0)
    {
        if ($sessionUrlId == 0) {
            $sessionUrl = SessionUrl::getLastVisitedUrl();
            if ($sessionUrl) {
                $sessionUrlId = $sessionUrl->id;
            }
        }
        
        $event = new self();
        $event->sessionUrlId = (int)$sessionUrlId;
        $event->eventName = (string)$name;
        $event->params = json_encode($params);
        $event->save();
    }
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'createDate',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }
}
