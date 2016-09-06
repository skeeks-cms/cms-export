<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 06.09.2016
 */
namespace skeeks\cms\export;
use skeeks\cms\base\ConfigFormInterface;
use skeeks\cms\export\helpers\ExportResult;
use skeeks\cms\export\models\ExportTask;
use skeeks\cms\export\widgets\ExportWidget;
use yii\base\Component;
use yii\base\Model;
use yii\bootstrap\Alert;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * @property string $rootFilePath
 * @property ExportResult $result
 *
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
     * @var string
     */
    public $file_path = '/export/file';

    public $alias = '@frontend/web';

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            ['file_path' , 'required'],
            ['file_path' , 'string'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'file_path'        => \Yii::t('skeeks/export', 'Путь к файлу'),
        ]);
    }

    /**
     * @param ActiveForm $form
     */
    public function renderConfigForm(ActiveForm $form)
    {
        echo $form->field($this, 'file_path')->textInput([
            'data-form-reload' => 'true'
        ]);

        if (file_exists($this->rootFilePath))
        {
            echo Alert::widget([
                'options' => [
                    'class' => 'alert-info',
                ],
                'body' => "Этот файл уже есть и будет перезаписан. Полный путь: {$this->rootFilePath}",
            ]);
        } else
        {
            echo Alert::widget([
                'options' => [
                    'class' => 'alert-info',
                ],
                'body' => "Этот файл будет создан. Полный путь: {$this->rootFilePath}",
            ]);
        }
    }

    /**
     * @return bool|string
     */
    public function getRootFilePath()
    {
        return \Yii::getAlias($this->alias  . $this->file_path);
    }

    /**
     * @param ActiveForm $form
     */
    public function renderWidget(ActiveForm $form)
    {
        echo ExportWidget::widget([
            'activeForm' => $form
        ]);
    }

    /**
     * @return ExportResult
     */
    public function export()
    {
        return $this->result;
    }

    /**
     * @var ExportResult
     */
    protected $_result = null;

    /**
     * @return null|ExportResult
     */
    public function getResult()
    {
        if (!$this->_result)
        {
            $this->_result = new ExportResult();
        }

        return $this->_result;
    }
}