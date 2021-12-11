<?php
/**
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use Yii;
use yii\base\Model;

/**
 * Class Event
 * @package willarin\tracker\models
 */
class Event extends Model
{
    /**
     * @event AfterSaveEvent tracking event
     */
    const EVENT_TRACK_EVENT = 'trackEvent';
    
    /**
     * @var boolean debug mode
     */
    public $debug = false;
    
    /**
     * @var array
     */
    public $trackingCodes = [];
    
    /**
     * @var string
     */
    public $result;
    
    /**
     * creates event with its setting by identifier
     *
     * @param string event identifier
     * @return Event|boolean
     */
    public static function getEvent($eventId)
    {
        $result = false;
        $tracking = Yii::$app->getModule('tracking');
        if ($tracking) {
            if ((isset($tracking->events[$eventId]['trackingCodes'])) and is_array($tracking->events[$eventId]['trackingCodes'])) {
                $result = new self($tracking->events[$eventId]);
            }
        }
        
        return $result;
    }
    
    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['debug'], 'boolean'],
        ];
    }
    
    /**
     * Launch all event tracking codes
     *
     * @param $urlParams array url params
     * @param $dataParams array data params
     * @return boolean
     */
    public function track($urlParams = [], $dataParams = [])
    {
        $result = true;
        foreach ($this->trackingCodes as $trackingCodeData) {
            $trackingCode = new TrackingCode($trackingCodeData);
            
            if ($trackingCode->type == 'postback') {
                $codeUrlParams = array_merge($trackingCode->urlParams, $urlParams);
                $codeDataParams = array_merge($trackingCode->dataParams, $dataParams);
                $url = $trackingCode->url;
                if (count($codeUrlParams) > 0) {
                    $url .= '?' . http_build_query($codeUrlParams);
                }
                $codeResult = $trackingCode->sendPostback($url, $codeDataParams);
                if (!$codeResult) {
                    $result |= $codeResult;
                    $this->result .= $codeResult->result . PHP_EOL;
                }
            }
        }
    }
}
