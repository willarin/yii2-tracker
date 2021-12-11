<?php
/**
 * @link https://github.com/willarin/yii2-tracker
 * @copyright Copyright (c) 2021 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\controllers;

use Exception;
use willarin\tracker\models\SessionEvent;
use willarin\tracker\models\SessionUrl;
use Yii;
use yii\web\Controller;

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
     * @throws Exception
     */
    public function actionDuration()
    {
        $request = Yii::$app->request;
        SessionUrl::saveAttribute('duration', $request->getQueryParam('url'), $request->getQueryParam('time', 0));
        return false;
    }
    
    /**
     * track scroll up or down, the request should be in the form of [url]/tracker/track/scroll?direction=[Up|Down]&number=[number]&url=[encodedUrl]
     *
     * @throws Exception
     */
    public function actionScroll()
    {
        $request = Yii::$app->request;
        $direction = $request->getQueryParam('direction');
        if ($direction !== 'Up') {
            $direction = 'Down';
        }
        SessionUrl::saveAttribute('scrolls' . $direction, $request->getQueryParam('url'), $request->getQueryParam('number', 1));
        return false;
    }
    
    
    /**
     * track event, the request should be in the form of [url]/tracker/track/index?name=[name]&params[param1]=[param1]&params[param2]=[param2][&sessionUrlId=[sessionUrlId]]
     *
     * @throws Exception
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        Yii::info(print_r($request->getQueryParams(), true), 'tracker');
        SessionEvent::saveEvent($request->getQueryParam('name', ''), $request->getQueryParam('params', ''), $request->getQueryParam('sessionUrlId', 0));
        return false;
    }
}
