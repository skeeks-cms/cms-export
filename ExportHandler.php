<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (�����)
 * @date 01.09.2016
 */
namespace skeeks\cms\export;
use skeeks\cms\base\ConfigFormInterface;
use skeeks\cms\export\models\ExportTask;
use yii\base\Component;
use yii\base\Model;
use yii\widgets\ActiveForm;

/**
 * Interface ExportHandlerInterface
 * @package skeeks\cms\export
 */
abstract class ExportHandler extends Model implements ExportHandlerInterface, ConfigFormInterface
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string Export type csv, xml or null
     */
    public $type;

    /**
     * @var ExportTask
     */
    public $taskModel;

    /**
     * @param ActiveForm $form
     */
    public function renderConfigForm(ActiveForm $form)
    {}

    /**
     * @param ActiveForm $form
     */
    public function renderWidget(ActiveForm $form)
    {
        echo 'Not found widget';
    }
}