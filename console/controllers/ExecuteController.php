<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.09.2016
 */
namespace skeeks\cms\export\console\controllers;
use skeeks\cms\agent\models\CmsAgent;
use skeeks\cms\components\Cms;
use skeeks\cms\export\helpers\ExportResultConsole;
use skeeks\cms\export\models\ExportTask;
use skeeks\cms\helpers\StringHelper;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Выполнение задач
 */
class ExecuteController extends Controller
{
    /**
     * Выполнить задачу
     * @param $id номер задачи
     */
    public function actionTask($id)
    {
        /**
         * @var ExportTask $exportTask
         */
        $exportTask = ExportTask::findOne(['id' => $id]);
        if (!$exportTask)
        {
            $this->stdout("Задача №{$id} не найдена.\n", Console::FG_RED);
            return false;
        }

        $this->stdout("Задача №{$id} — {$exportTask->name}.\n", Console::BOLD);
        $handler = $exportTask->handler;
        if (!$handler)
        {
            $this->stdout("Не найден обработчик $exportTask->component\n", Console::FG_RED);
            print_r(array_keys(\Yii::$app->cmsExport->handlers));
            return false;
        }

        $handler->setResult(new ExportResultConsole([
            'controller' => $this
        ]));
        $exportTask->handler->export();
    }
}