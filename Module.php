<?php
/**
 * @copyright Copyright (c) 2020 Solutlux LLC
 * @license https://opensource.org/licenses/BSD-3-Clause BSD License (3-clause)
 */

namespace willarin\tracker;

/**
 * The tracker module provides functionality of the 3rd party tracking via different methods
 *
 * @author Solutlux LLC
 */
class Module extends \yii\base\Module
{
    /**
     * @var array list of settings parameters to be available for all trackers
     */
    public $settings = [];

    /**
     * @var array list of tracking schemas available
     */
    public $trackers = [];
}
