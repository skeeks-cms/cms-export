<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.08.2016
 */
namespace skeeks\cms\export\widgets;

use skeeks\cms\export\models\ExportTask;
use skeeks\cms\importCsv\models\ImportTaskCsv;
use skeeks\cms\importCsv\widgets\assets\ImportWidgetAsset;
use skeeks\cms\importCsvContent\CsvContentHandler;
use yii\base\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\InputWidget;

/**
 * @property CsvContentHandler $model
 *
 * Class MatchingInput
 *
 * @package skeeks\cms\import\widgets
 */
class ExportWidget extends Widget
{
    public static $autoIdPrefix = 'export';

    public $clientOptions = [];
    public $options = [];

    public $showButton = true;
    public $buttonOptions = [];

    /**
     * @var ExportTask
     */
    public $modelTask = null;

    /**
     * @var ActiveForm
     */
    public $activeForm = null;

    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }


        $this->clientOptions = ArrayHelper::merge([
            'backend'       => \skeeks\cms\helpers\UrlHelper::construct(['/cmsExport/admin-export-task/export'])->enableAdmin()->toString(),
            'id'                => $this->options['id'],
            'formId'            => $this->activeForm->id,
        ], $this->clientOptions);


        Html::addCssClass($this->options, 'sx-export-widget');
        Html::addCssClass($this->buttonOptions, 'sx-start-btn btn btn-primary btn-lg');

        parent::init();

    }

    public function run()
    {
        try
        {
            echo $this->render('export');

        } catch (\Exception $e)
        {
            echo $e->getMessage();
        }
    }

}