<?php
/**
 * Session event recording functions
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;

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
     * checks if such event was already saved in session before and if not found save event
     * @param $sessionUrlId int
     * @param $eventName string
     * @param $params array
     */
    public static function saveOnlyEventInSession(int $sessionUrlId, string $eventName, array $params = [])
    {
        $schema = Yii::$app->db->getSchema();
        $eventsQuery = SessionUrl::find()
            ->innerJoin(['su' => 'SessionUrl'], $schema->quoteColumnName('su.sessionId') . '=' . $schema->quoteColumnName('SessionUrl.sessionId'))
            ->innerJoin(['se' => 'SessionEvent'], $schema->quoteColumnName('se.sessionUrlId') . '=' . $schema->quoteColumnName('su.id'))
            ->where(['SessionUrl.id' => $sessionUrlId])
            ->andWhere(['se.eventName' => $eventName]);
        if ($eventsQuery->count() == 0) {
            self::saveEvent($sessionUrlId, $eventName, $params);
        }
    }
    
    /**
     * save event
     * @param $sessionUrlId int
     * @param $eventName string
     * @param $params array
     */
    public static function saveEvent(int $sessionUrlId, string $eventName, array $params = [])
    {
        $sessionEvent = new self();
        $sessionEvent->sessionUrlId = $sessionUrlId;
        $sessionEvent->eventName = $eventName;
        $sessionEvent->params = json_encode($params);
        $sessionEventSave = $sessionEvent->save();
        
        //\Yii::info(' Event: ' . $sessionEvent->id . PHP_EOL . print_r($request->getQueryParams(), true) . PHP_EOL, 'tracker');
        if ($sessionEventSave) {
            $event = Event::getEvent($sessionEvent->eventName);
            if ($event) {
                $event->sessionEventId = (int)$sessionEvent->id;
                $event->track();
            }
        }
    }
    
    /**
     * save event
     */
    public static function saveEventFromUrl()
    {
        $request = Yii::$app->request;
        $sessionUrl = SessionUrl::updateCookieParams();
        
        if ($sessionUrl) {
            $params = $request->getQueryParam('params', []);
            self::saveEvent($sessionUrl->id, $request->getQueryParam('name', ''), $params);
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
