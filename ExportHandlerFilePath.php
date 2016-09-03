<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 03.09.2016
 */
namespace skeeks\cms\export;
use skeeks\cms\base\ConfigFormInterface;
use skeeks\cms\export\models\ExportTask;
use yii\base\Component;
use yii\base\Model;
use yii\console\ErrorHandler;
use yii\widgets\ActiveForm;

/**
 * Interface ExportHandlerInterface
 * @package skeeks\cms\export
 */
class ExportHandlerFilePath extends ErrorHandler
{
    /**
     * @var string
     */
    public $file_path = '/export/file';

    /**
     * @param ActiveForm $form
     */
    public function renderConfigForm(ActiveForm $form)
    {

    }

    /**
     * @param ActiveForm $form
     */
    public function renderWidget(ActiveForm $form)
    {
    }
}