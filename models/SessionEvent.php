<?php
/**
 * Session event recording functions
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQueryInterface;
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
    
    /**
     * save event
     */
    public static function saveEvent()
    {
        $request = Yii::$app->request;
        $sessionUrl = SessionUrl::updateCookieParams();
    
        if ($sessionUrl) {
            $sessionEvent = new self();
            $sessionEvent->sessionUrlId = $sessionUrl->id;
            $sessionEvent->eventName = $request->getQueryParam('name', '');
            $sessionEvent->params = json_encode($request->getQueryParam('params', ''));
            $sessionEventSave = $sessionEvent->save();
        
            //\Yii::info(' Event: ' . $sessionEvent->id . PHP_EOL . print_r($request->getQueryParams(), true) . PHP_EOL, 'tracker');
            if ($sessionEventSave) {
                $event = Event::getEvent($sessionEvent->eventName);
                if ($event) {
                    $event->sessionEventId = (int)$sessionEvent->id;
                    $event->track();
                }
            }
        } else {
            Yii::error('Session Url not found with query params: ' . print_r(Yii::$app->request->getQueryParams(), true), 'tracker');
        }
    }
    
    /**
     * get SessionUrl object
     *
     * @return ActiveQueryInterface
     */
    public function getSessionUrl()
    {
        return $this->hasOne(SessionUrl::className(), ['id' => 'sessionUrlId']);
    }
}
