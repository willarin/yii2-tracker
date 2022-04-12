<?php
/**
 * Session recording functions
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Yii;
use Jenssegers\Agent\Agent;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;

/**
 * Class Session
 * @package willarin\tracker\models
 */
class Session extends ActiveRecord
{
    /**
     * {@inheritDoc}
     */
    public static function tableName()
    {
        return 'Session';
    }
    
    /**
     * Return current session or register new if session doesn't exists. Registers device and browser  data
     * @return bool|Session
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function getCurrent()
    {
        $session = self::find()->where(['sessionStringId' => static::getId()])->one();
        if (!$session) {
            $request = Yii::$app->request;
            
            $session = new self();
            
            $session->sessionStringId = self::getId();
            $session->personId = Person::getId($session->sessionStringId);
            $session->cookieParams = json_encode($request->getCookies()->toArray());
            $session->serverParams = json_encode(@$_SERVER);
            
            $agent = new Agent();
            $session->type = $agent->isMobile() ? 'mobile' : ($agent->isTablet() ? 'tablet' : 'desktop');
            $session->model = $agent->device();
            $session->platform = $agent->platform();
            $session->platformVersion = $agent->version($session->platform);
            $browser = $agent->browser();
            $session->browser = $browser . ' ' . $agent->version($browser);
            
            $session->isBot = $agent->isRobot();
            $session->save(false);
        }
        return $session;
    }
    
    /**
     * Returns current session id
     * @return string
     */
    public static function getId()
    {
        $session = Yii::$app->session;
        if (!$session->isActive) {
            $session->open();
        }
        return $session->getId();
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
                'updatedAtAttribute' => 'updateDate',
                'value' => new Expression('NOW()'),
            ],
        ];
    }
    
    /**
     * add array of cookie params
     *
     * @param array $params
     */
    public function addCookieParams(array $params)
    {
        if ((is_array($params)) and (count($params) > 0)) {
            $cookieParamsStorage = (array)json_decode($this->cookieParams);
            $update = false;
            foreach ($params as $cookieParamName => $cookieParamValue) {
                if (!isset($cookieParamsStorage[$cookieParamName])) {
                    $cookieParamsStorage[$cookieParamName] = [
                        'name' => $cookieParamName,
                        'value' => $cookieParamValue
                    ];
                    $update = true;
                }
            }
            if ($update) {
                $this->cookieParams = json_encode($cookieParamsStorage);
                $this->save();
            }
        }
    }
}
