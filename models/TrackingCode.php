<?php
/**
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use ReflectionClass;
use ReflectionObject;
use Yii;
use yii\base\Model;

/**
 * Class TrackingCode
 * @package willarin\tracker\models
 */
class TrackingCode extends Model
{
    /**
     * @var boolean debug mode
     */
    public $debug = false;
    
    /**
     * @var string event type
     */
    public $type = 'postback';
    
    /**
     * @var string
     */
    public $url;
    
    /**
     * @var array
     */
    public $headers = [];
    
    /**
     * @var array
     */
    public $urlParams = [];
    
    /**
     * @var array
     */
    public $dataParams = [];
    
    /**
     * @var string name of the function to retrieve url parameters
     */
    public $urlParamsFunction;
    
    /**
     * @var string name of the function to retrieve data parameters
     */
    public $dataParamsFunction;
    
    /**
     * @var string
     */
    public $result;
    
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
     * Send postback
     *
     * @param $postbackUrl string postback url point
     * @param $postbackData array postback data list
     * @return string curl request result
     */
    public function sendPostback($postbackUrl, $postbackData = [])
    {
        $result = false;
        
        //sent postback
        if (is_array($postbackData) && $postbackUrl) {
            if (!$this->debug) {
                $curl = curl_init();
    
                curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
                curl_setopt($curl, CURLOPT_URL, $postbackUrl);
    
                if ($this->type == 'postback') {
                    $postbackDataEncoded = http_build_query($postbackData);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $postbackDataEncoded);
                }
    
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_VERBOSE, true);
                curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    
                $verbose = fopen('php://temp', 'w+');
    
                curl_setopt($curl, CURLOPT_STDERR, $verbose);
    
                $postbackResult = curl_exec($curl);
    
                $info = curl_getinfo($curl, CURLINFO_HEADER_OUT);
    
                rewind($verbose);
    
                $verboseLog = stream_get_contents($verbose);
    
                $postbackResultText = ((is_array(@$postbackResult)) ? print_r($postbackResult, true) : @$postbackResult);
                Yii::info('URL: ' . $postbackUrl . PHP_EOL .
                    'headers: ' . print_r($this->headers, true) . PHP_EOL .
                    'verbose log ' . print_r($verboseLog, true) . PHP_EOL .
                    'request: ' . print_r($info, true) . PHP_EOL .
                    'data: ' . print_r($postbackData, true) . PHP_EOL .
                    'result: ' . $postbackResultText . PHP_EOL, 'tracker');
    
                if ($postbackResult) {
                    $result = true;
                } else {
                    $this->result = $postbackResultText;
                }
            } else {
                $result = true;
            }
        }
        
        return $result;
    }
    
    /**
     * build url using parameters
     *
     * @param $url string url with patterns
     * @param $params
     * @param bool $addMissingParams
     * @return mixed|string
     */
    public static function buildUrl($url, $params, $addMissingParams = false)
    {
        if (preg_match_all('/{(.*)}/Ui', $url, $matches)) {
            //replace matched parameters
            foreach ($matches[1] as $var) {
                $value = null;
                $data = explode('|', $var);
                $fieldNames = explode(',', $data[0]);
                foreach ($fieldNames as $fieldName) {
                    if (isset($params[$fieldName])) {
                        $value = $params[$fieldName];
                        unset($params[$fieldName]);
                        break;
                    }
                }
                if ($value == null) {
                    $value = @$data[1]; //default value
                }
                $url = str_replace('{' . $var . '}', urlencode($value), $url);
            }
            if ($addMissingParams) { //add missing parameters
                foreach ($params as $key => $value) {
                    $url .= (strpos($url, '?') === false ? '?' : '&') . $key . '=' . urlencode($value);
                }
            }
        }
        return $url;
    }
    
    /**
     * retrieve parameters with function request
     *
     * @param Event $event
     * @param string $paramsFunction name of params function
     * @param array $params list of parameters to be sent for tracking function
     *
     * @return boolean|string
     */
    public function getFunctionParams($event, $paramsFunction, $params = [])
    {
        $result = [];
        if ($paramsFunction) {
            if (is_object($event->context)) {
                $reflection = new ReflectionObject($event->context);
            } else {
                $reflection = new ReflectionClass($event->context);
                $event->context = $reflection->newInstance();
            }
            if ($reflection->hasMethod($paramsFunction)) {
                $dataMethod = $reflection->getMethod($paramsFunction);
                $params = array('sessionEventId' => $event->sessionEventId) + $params;
                $trackingCodeDataParams = $dataMethod->invokeArgs($event->context, $params);
                if ((is_array($trackingCodeDataParams)) and (count($trackingCodeDataParams) > 0)) {
                    $result = $trackingCodeDataParams;
                } else {
                    $result = false;
                }
            }
        }
        return $result;
    }
}
