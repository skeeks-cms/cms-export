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
use skeeks\cms\models\CmsSite;
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
 * @property integer       $cms_site_id
 * 
 * @property CmsSite       $cmsSite
 * @property ExportHandler $handler
 */
class ExportTask extends \skeeks\cms\models\Core
{
    public function enqueue($triggerType = 'manual')
    {
        if ($this->isNewRecord || !$this->handler) {
            throw new \RuntimeException('Сначала сохраните настройку экспорта.');
        }
        $handler = $this->handler;
        if (!$handler->validate()) { throw new \InvalidArgumentException(implode('; ', $handler->getFirstErrors())); }
        $run = \Yii::$app->jobs->push(\skeeks\cms\export\jobs\ExportJobHandler::TYPE,
            ['export_task_id' => (int)$this->id], [
                'siteId' => $this->cms_site_id,
                'title' => 'Экспорт: '.$this->name,
                'triggerType' => $triggerType,
                'triggerRef' => 'export_task:'.$this->id,
            ]);
        return $run ?: $this->getLatestJob(true);
    }

    public function getLatestJob($activeOnly = false)
    {
        $key = 'export:'.(int)$this->cms_site_id.':'.(int)$this->id;
        return \skeeks\cms\job\models\CmsJobRun::find()->andWhere([
            $activeOnly ? 'dedup_active' : 'dedup_key' => $key,
            'cms_site_id' => $this->cms_site_id,
            'job_type' => \skeeks\cms\export\jobs\ExportJobHandler::TYPE,
        ])->orderBy(['id' => SORT_DESC])->one();
    }

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
            
            [['cms_site_id',], 'integer'],

            [
                'cms_site_id',
                'default',
                'value' => function () {
                    if (\Yii::$app->skeeks->site) {
                        return \Yii::$app->skeeks->site->id;
                    }
                },
            ],
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
                $component = clone \Yii::$app->cmsExport->getHandler($this->component);
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
    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCmsSite()
    {
        $class = \Yii::$app->skeeks->siteClass;
        return $this->hasOne($class, ['id' => 'cms_site_id']);
    }
}
