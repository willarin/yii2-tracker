<?php
/**
 * Session URL visit recording functions
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Exception;
use willarin\tracker\behaviors\AttrCutBehavior;
use yii\base\Event;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQueryInterface;
use yii\db\ActiveRecord;
use yii\db\Expression;
use Yii;

/**
 * Class SessionUrl
 * @package willarin\tracker\models
 */
class SessionUrl extends ActiveRecord
{
    /**
     * @var string gateway request event
     */
    const EVENT_UPDATE_ATTRIBUTE = 'updateAttribute';
    
    /**
     * @return string the name of the index associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return 'SessionUrl';
    }
    
    /**
     * {@inheritDoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors[] = [
            'class' => AttrCutBehavior::class
        ];
        $behaviors[] = [
            'class' => TimestampBehavior::class,
            'createdAtAttribute' => 'createDate',
            'updatedAtAttribute' => false,
            'value' => new Expression('NOW()'),
        ];
        
        return $behaviors;
    }
    
    /**
     * save user's url visit
     * @param string $url url of the last visited page
     *
     * @return bool/SessionUrl
     */
    public static function saveUrlVisit($url = '')
    {
        $result = false;
        try {
            $session = Session::getCurrent();
            
            if ($session) {
                $scdid = (int)Yii::$app->request->getQueryParam('scdid', 0);
                //update previous session (from another domain)
                if ($scdid > 0) {
                    $previousSession = Session::findOne($scdid);
                    if (($previousSession) and ($previousSession->id <> $session->id)) {
                        $previousSession->replaceSessionUrls($session->id);
                    }
                }
                $sessionUrl = new self();
                $sessionUrl->sessionId = $session->id;
                if ($url) {
                    $sessionUrl->visitedUrl = urldecode($url);
                } else {
                    $sessionUrl->visitedUrl = urldecode(Yii::$app->request->getAbsoluteUrl());
                }
                $sessionUrl->save();
                $result = $sessionUrl;
            }
        } catch (Exception $e) {
        }
        return $result;
    }
    
    /**
     * get current sessionUrl
     *
     * @param string $url url of the last visited page
     * @param integer $sessionUrlId SessionUrl identifier
     * @return mixed bool|SessionUrl
     */
    public static function getCurrent($url = '', int $sessionUrlId = 0)
    {
        $sessionUrl = false;
        
        if ((int)$sessionUrlId > 0) {
            $sessionUrl = self::findOne((int)$sessionUrlId);
        }
        
        if (!$sessionUrl) {
            $sessionUrl = self::getLastVisitedUrl($url);
        }
        
        return $sessionUrl;
    }
    
    /**
     * get current sessionUrl id
     *
     * @return bool|integer
     */
    public static function getCurrentUrlId()
    {
        $result = false;
        $request = Yii::$app->request;
        $sessionUrl = self::getLastVisitedUrl();
        if ($sessionUrl) {
            $result = $sessionUrl->id;
        }
        return $result;
    }
    
    /**
     * Retrieve the last visited URL from database
     * @param string $url url of the last visited page
     *
     * @return array|ActiveRecord|null
     */
    public static function getLastVisitedUrl($url = '')
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
     * update cookie params with GET request
     *
     * @return mixed bool|SessionUrl
     */
    public static function updateCookieParams()
    {
        $sessionUrl = false;
        $request = Yii::$app->request;
        $sessionUrl = SessionUrl::getCurrent($request->getQueryParam('url', ''), (int)$request->getQueryParam('sessionUrlId', 0));
        $cookieParams = $request->getQueryParam('cookieParams');
        if (($sessionUrl) && (is_array($cookieParams))) {
            $sessionUrl->session->addCookieParams($cookieParams);
        }
        return $sessionUrl;
    }
    
    /**
     * Save SessionUrl attribute with SessionUrl retieved by id or url
     *
     * @param string $attribute attribute identifier
     * @param string $url url of the last visited page
     * @param integer $value attribute value to be saved
     * @param integer $sessionUrlId SessionUrl identifier
     *
     * @return bool|array either false or SessionUrl atributes
     */
    public static function saveAttribute($attribute, $url, $value, int $sessionUrlId = 0)
    {
        $result = false;
        $sessionUrl = self::getCurrent($url, $sessionUrlId);
        if (($sessionUrl) and ($sessionUrl->hasAttribute($attribute))) {
            $sessionUrl->setAttribute($attribute, $value);
            $sessionUrl->trigger(self::EVENT_UPDATE_ATTRIBUTE, new Event());
            if ($sessionUrl->save()) {
                $result = $sessionUrl->attributes;
            }
        }
        return $result;
    }
    
    /**
     * get session object
     *
     * @return ActiveQueryInterface
     */
    public function getSession()
    {
        return $this->hasOne(Session::class, ['id' => 'sessionId']);
    }
}
