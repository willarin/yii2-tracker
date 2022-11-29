<?php

namespace willarin\tracker\behaviors;

use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class AttrCutBehavior
 * @package willarin\tracker\behaviors
 */
class AttrCutBehavior extends Behavior
{

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
        ];
    }

    /**
     * @param $event
     */
    public function afterValidate($event)
    {
        $attributes = $this->owner->attributes;
        foreach ($attributes as $key => $value) {
            $column = $this->owner->getTableSchema()->getColumn($key);
            if (($column->phpType == 'string') && ($column->size && $column->size < strlen($this->owner->$key))) {
                $this->owner->$key = substr($this->owner->$key, 0, $column->size - 1);
            }
        }
    }
}
