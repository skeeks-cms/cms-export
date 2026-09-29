<?php
namespace skeeks\cms\export\widgets;

class ExportWidget extends \yii\base\Widget
{
    public $clientOptions = [];
    public $options = [];
    public $showButton = true;
    public $buttonOptions = [];
    public $modelTask;
    public $activeForm;

    public function run()
    {
        if (!$this->showButton) { return ''; }
        if (!$this->modelTask || $this->modelTask->isNewRecord) {
            return \yii\helpers\Html::tag('p', 'Сохраните настройку, чтобы запустить экспорт в фоне.');
        }
        return \yii\helpers\Html::tag('p', 'Запускается сохранённая настройка. После запуска можно закрыть страницу.').
            \skeeks\cms\job\widgets\JobButton::widget([
                'startUrl' => ['/cmsExport/admin-export-task/start-job', 'id' => $this->modelTask->id],
                'statusUrl' => ['/cmsExport/admin-export-task/job-status', 'id' => $this->modelTask->id],
            ]);
    }
}
