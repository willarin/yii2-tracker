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
        $sessionUrlId = $request->getQueryParam('sessionUrlId', 0);
        if ($sessionUrlId == 0) {
            $sessionUrl = SessionUrl::getLastVisitedUrl($request->getQueryParam('url'));
            if ($sessionUrl) {
                $sessionUrlId = $sessionUrl->id;
            }
        }
        
        $sessionEvent = new self();
        $sessionEvent->sessionUrlId = (int)$sessionUrlId;
        $sessionEvent->eventName = $request->getQueryParam('name', '');
        $sessionEvent->params = json_encode($request->getQueryParam('params', ''));
        $sessionEventSave = $sessionEvent->save();
        
        $cookieParams = $request->getQueryParam('cookieParams');
        if (is_array($cookieParams)) {
            if (!isset($sessionUrl)) {
                $sessionUrl = SessionUrl::findOne((int)$sessionUrlId);
            }
            if ($sessionUrl) {
                $cookieParamsStorage = json_decode($sessionUrl->session->cookieParams);
                foreach ($cookieParams as $cookieParamName => $cookieParamValue) {
                    if (!isset($cookieParamsStorage->{$cookieParamName})) {
                        $cookieParamsStorage[$cookieParamName] = [
                            'name' => $cookieParamName,
                            'value' => $cookieParamValue
                        ];
                        
                        $sessionUrl->session->cookieParams = json_encode($cookieParamsStorage);
                        $sessionUrl->session->save();
                    }
                }
            }
        }
        
        if ($sessionEventSave) {
            $event = Event::getEvent($sessionEvent->eventName);
            if ($event) {
                $event->sessionEventId = (int)$sessionEvent->id;
                $event->track();
            }
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
