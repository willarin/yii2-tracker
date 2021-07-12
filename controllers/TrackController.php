<?php
/**
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\controllers;

use yii\web\Controller;
use yii\web\Response;

/**
 * TrackController provides functionality of tracking via different methods (postbacks, html code of widget). It is based on parameters settings passed to controller.
 *
 * @author Solutlux LLC
 */
class TrackController extends Controller
{
	
	public $enableCsrfValidation = false;
	
	public function actionIndex()
	{

	}

}
