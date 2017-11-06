<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.08.2016
 */
namespace skeeks\cms\export\models;

use skeeks\cms\export\ExportHandler;
use skeeks\cms\export\ExportHandlerInterface;
use skeeks\cms\models\behaviors\Serialize;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%export_task}}".
 *
 * @property integer $id
 * @property integer $created_by
 * @property integer $updated_by
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $description
 * @property string $component
 * @property string $component_settings
 *
 * @property ExportHandler $handler
 */
class ExportTask extends \skeeks\cms\models\Core
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%export_task}}';
    }

    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            Serialize::className() =>
            [
                'class' => Serialize::className(),
                'fields' => ['component_settings']
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['component'], 'required'],
            [['component_settings'], 'safe'],
            [['description'], 'string'],
            [['name', 'component'], 'string', 'max' => 255],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'id' => Yii::t('skeeks/export', 'ID'),
            'name' => Yii::t('skeeks/export', 'Name'),
            'description' => Yii::t('skeeks/export', 'Description'),
            'component' => Yii::t('skeeks/export', 'Component'),
            'component_settings' => Yii::t('skeeks/export', 'Component Settings'),
        ]);
    }

    /**
     * @return ExportHandlerInterface
     * @throws \skeeks\cms\export\InvalidParamException
     */
    public function getHandler()
    {
        if ($this->component)
        {
            try
            {
                /**
                 * @var $component Component
                 */
                $component = \Yii::$app->cmsExport->getHandler($this->component);
                $component->taskModel = $this;
                $component->load($this->component_settings, "");

                return $component;
            } catch (\Exception $e)
            {
                return false;
            }

        }

        return null;
    }
}