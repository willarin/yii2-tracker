<?php
/**
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker\assets;

use yii\web\AssetBundle;

/**
 * Asset for tracking bundle - js files to send duration and scroll values.
 */
class TrackAsset extends AssetBundle
{
    public $sourcePath = '@vendor/willarin/yii2-tracker/assets';
    public $js = [
        'js/tracking.js'
    ];
}
