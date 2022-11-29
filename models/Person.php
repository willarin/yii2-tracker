<?php
/**
 * Person handler
 *
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\Cookie;

/**
 * Class Person
 * @package willarin\tracker\models
 */
class Person extends ActiveRecord
{
    
    /**
     * {@inheritdoc}
     * @return string
     */
    public static function tableName()
    {
        return 'Person';
    }
    
    /**
     * Returns current person id
     *
     * @param string
     * @return string $personIdentifier person string id recorded into cookie
     */
    public static function getId($personIdentifier)
    {
        $cookieStringId = Yii::$app->request->cookies->get('personId');
        if ($cookieStringId === null) {
            $cookieStringId = $personIdentifier;
        }
        
        $person = self::find()->where(['cookieStringId' => $cookieStringId])->one();
        if (!$person) {
            Yii::$app->response->cookies->add(new Cookie([
                'name' => 'personId',
                'value' => $personIdentifier,
                'path' => '/',
                'expire' => time() + (10 * 365 * 24 * 60 * 60),
                'httpOnly' => false,
            ]));
            $person = new static();
            $person->cookieStringId = $personIdentifier;
            $person->save();
        }
        return $person->getPrimaryKey();
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
