<?php
/**
 * Session URL visit recording functions
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Class SessionUrl
 * @package willarin\tracker\models
 */
class SessionUrl extends ActiveRecord
{
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return 'SessionUrl';
    }
    
    /**
     * save user's url visit
     */
    public static function saveUrlVisit()
    {
        $session = Session::getCurrent();
        if ($session) {
            $sessionUrl = new self();
            $sessionUrl->sessionId = $session->id;
            $sessionUrl->visitedUrl = Yii::$app->request->getAbsoluteUrl();
            $sessionUrl->save();
        }
    }
    
    /**
     * get current session Url id
     *
     * @return bool|integer
     */
    public static function getCurrentUrlId()
    {
        $result = false;
        
        $sessionUrl = self::getLastVisitedUrl();
        if ($sessionUrl) {
            $result = $sessionUrl->id;
        }
        return $result;
    }
    
    /**
     * Retrieve the last visited URL from database
     * @param bool|string $url url of the last visited page
     *
     * @return array|ActiveRecord|null
     */
    public static function getLastVisitedUrl($url = false)
    {
        $result = null;
        $session = Session::findOne(['sessionStringId' => Session::getId()]);
        if ($session) {
            $query = self::find()
                ->where(['sessionId' => $session->id]);
            if ($url) {
                $query->andWhere(['visitedUrl' => urldecode($url)]);
            }
            $result = $query->orderBy(['id' => SORT_DESC])
                ->one();
        }
        return $result;
    }
    
    /**
     * save user's visit duration
     *
     * @param string $url url of the last visited page
     * @param integer $duration seconds user's spent at the url
     */
    public static function saveDuration($url, $duration)
    {
        self::saveAttribute('duration', $url, $duration);
    }
    
    /**
     * save session Url attribute
     *
     * @param string $attribute attribute identifier
     * @param string $url url of the last visited page
     * @param integer $value seconds user's spent at the url
     */
    public static function saveAttribute($attribute, $url, $value)
    {
        $sessionUrl = self::getLastVisitedUrl($url);
        if (($sessionUrl) && (isset($sessionUrl->{$attribute}))) {
            $sessionUrl->{$attribute} = $value;
            $sessionUrl->save();
        }
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
