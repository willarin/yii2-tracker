<?php
/**
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\controllers;

use Exception;
use Yii;
use willarin\tracker\models\SessionEvent;
use willarin\tracker\models\SessionUrl;
use yii\web\Controller;
use yii\web\Response;

/**
 * TrackController provides functionality of tracking via different methods (postbacks, html code of widget). It is based on parameters settings passed to controller.
 *
 * @author Solutlux LLC
 */
class TrackController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $enableCsrfValidation = false;
    
    
    /**
     * track urt visit duration, the request should be in the form of [url]/tracker/track/duration?time=[time]&url=[encodedUrl]
     *
     * @return bool|array either false or SessionUrl atributes
     * @throws Exception
     */
    public function actionDuration()
    {
        $result = false;
        $request = Yii::$app->request;
        if (($request->getQueryParam('sessionUrlId') !== null) || ($request->getQueryParam('url') !== null)) {
            $result = SessionUrl::saveAttribute('duration', $request->getQueryParam('url'), $request->getQueryParam('time', 0), (int)$request->getQueryParam('sessionUrlId'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }
    
    /**
     * track scroll up or down, the request should be in the form of [url]/tracker/track/scroll?direction=[Up|Down]&number=[number]&url=[encodedUrl]
     *
     * @return bool|array either false or SessionUrl atributes
     * @throws Exception
     */
    public function actionScroll()
    {
        $result = false;
        $request = Yii::$app->request;
        $direction = $request->getQueryParam('direction');
        if ($direction !== 'Up') {
            $direction = 'Down';
        }
        if (($request->getQueryParam('sessionUrlId') !== null) || ($request->getQueryParam('url') !== null)) {
            $result = SessionUrl::saveAttribute('scrolls' . $direction, $request->getQueryParam('url'), $request->getQueryParam('number', 1), (int)$request->getQueryParam('sessionUrlId'));
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }
    
    
    /**
     * track event, the request should be in the form of [url]/tracker/track/index?name=[name]&params[param1]=[param1]&params[param2]=[param2][&sessionUrlId=[sessionUrlId]]
     *
     * @throws Exception
     */
    public function actionIndex()
    {
        $sessionUrlId = (int)Yii::$app->request->getQueryParam('sessionUrlId');
        $eventName = Yii::$app->request->getQueryParam('name', '');
        $params = Yii::$app->request->getQueryParam('params', []);
        if (!is_array($params)) {
            $params = [$params];
        }
    
        if ($sessionUrlId > 0) {
            SessionEvent::saveOnlyEventInSession($sessionUrlId, $eventName, $params);
        }
        return false;
    }
    
    /**
     * update cookieParams data of session
     */
    public function actionCookiesData()
    {
        SessionUrl::updateCookieParams();
        return false;
    }
    
    /**
     * track urt visit, the request should be in the form of [url]/tracker/track/duration?time=[time]&url=[encodedUrl]
     *
     * @return bool|array either false or SessionUrl identifier
     * @throws Exception
     */
    public function actionVisit()
    {
        $result = false;
        $request = Yii::$app->request;
        $url = $request->getQueryParam('url', '');
        if ($url) {
            $sessionUrl = SessionUrl::saveUrlVisit($url);
            if (($sessionUrl) and (isset($sessionUrl->id))) {
                $result = $sessionUrl->id;
            }
        }
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }
    
}
