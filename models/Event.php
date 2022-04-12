<?php
/**
 * @copyright Copyright (c) 2020 Solutlux LLC
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
     * @var object
     */
    public $context;
    
    /**
     * @var integer linked SessionEvent identifier
     */
    public $sessionEventId = 0;
    
    /**
     * creates event with its setting by identifier
     *
     * @param string $eventId event identifier
     * @param bool|string|object $context context object for event
     * @return Event|boolean
     */
    public static function getEvent($eventId, $context = false)
    {
        $result = false;
        $tracker = Yii::$app->getModule('tracker');
        if ($tracker) {
            if ((isset($tracker->events[$eventId]['trackingCodes'])) and is_array($tracker->events[$eventId]['trackingCodes'])) {
                $eventData = $tracker->events[$eventId];
                if ($context) {
                    $eventData['context'] = $context;
                }
                $result = new self($eventData);
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
            $dataFunctionParams = $trackingCode->getFunctionParams($this, $trackingCode->dataParamsFunction, $trackingCode->dataParams);
            if ($trackingCode->type == 'postback') {
                $urlFunctionParams = $trackingCode->getFunctionParams($this, $trackingCode->urlParamsFunction, $trackingCode->urlParams);
                if (($dataFunctionParams !== false) && ($urlFunctionParams !== false)) {
                    $codeUrlParams = array_merge($trackingCode->urlParams, $urlParams, $urlFunctionParams);
                    $codeDataParams = array_merge($trackingCode->dataParams, $dataParams, $dataFunctionParams);
        
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
}
