<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 29.08.2016
 */
namespace skeeks\cms\export\helpers;

use yii\base\Component;

class ExportResult extends Component
{
    public $success     = true;
    public $message     = '';

    protected $_stdouts = [];

    /**
     * @param $message
     *
     * @return $this
     */
    public function stdout($message)
    {
        $this->_stdouts[] = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode("", $this->_stdouts);
    }
}