<?php
/**
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\models;

use linslin\yii2\curl;
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
    
    public $settings;
    
    /**
     * @var boolean debug mode
     */
    public $debug = false;
    
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
        $result = '';
        
        //sent postback
        if (is_array($postbackData) && $postbackUrl) {
            if (!$this->debug) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $postbackUrl);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postbackData));
                $postbackResult = curl_exec($curl);
            }
            $result = 'URL: ' . $postbackUrl . PHP_EOL .
                'data: ' . print_r($postbackData, true) . PHP_EOL .
                'result: ' . ((is_array(@$postbackResult)) ? print_r($postbackResult, true) : @$postbackResult) . PHP_EOL . PHP_EOL;
        }
        
        return $result;
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
        $result = '';
    
        //sent postback
        if (is_array($postbackData) && $postbackUrl) {
            if (!$this->debug) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $postbackUrl);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postbackData));
                $postbackResult = curl_exec($curl);
            }
            $result = 'URL: ' . $postbackUrl . PHP_EOL .
                'data: ' . print_r($postbackData, true) .
                'result: ' . ((is_array(@$postbackResult)) ? print_r($postbackResult, true) : @$postbackResult) . PHP_EOL . PHP_EOL;
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
        if (preg_match_all('/\{(.*)\}/Ui', $url, $matches)) {
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
}
