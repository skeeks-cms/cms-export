<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.04.2016
 */
namespace skeeks\cms\export\controllers;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\export\models\ExportTask;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\modules\admin\controllers\AdminController;
use skeeks\cms\modules\admin\controllers\AdminModelEditorController;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/**
 * Class AdminExportTaskController
 * @package skeeks\cms\export\controllers
 */
class AdminExportTaskController extends AdminModelEditorController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name                 = \Yii::t('skeeks/export', 'Tasks on exports');
        $this->modelShowAttribute   = "id";
        $this->modelClassName       = ExportTask::className();
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(),
        [
            'create' =>
            [
                'callback'         => [$this, 'create'],
            ],

            'update' =>
            [
                'callback'         => [$this, 'update'],
            ],
        ]);
    }


    public function create()
    {
        $rr = new RequestResponse();

        $model = new ExportTask();
        $model->loadDefaultValues();

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                $model->component_settings = $handler->toArray();
                if ($model->load(\Yii::$app->request->post()) && $handler->load(\Yii::$app->request->post())
                    && $model->validate() && $handler->validate())
                {
                    $model->save();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('app','Saved'));

                    return $this->redirect(
                        $this->indexUrl
                    );

                } else
                {
                    \Yii::$app->getSession()->setFlash('error', \Yii::t('app','Could not save'));
                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'handler'   => $handler,
        ]);
    }


    public function update()
    {
        $rr = new RequestResponse();

        $model = $this->model;

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost())
        {
            if (!\Yii::$app->request->post($this->notSubmitParam))
            {
                if ($rr->isRequestPjaxPost())
                {
                    $model->component_settings = $handler->toArray();

                    if ($model->load(\Yii::$app->request->post()) && $handler->load(\Yii::$app->request->post())
                        && $model->validate() && $handler->validate())
                    {
                        $model->save();

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('app','Saved'));

                        if (\Yii::$app->request->post('submit-btn') == 'apply')
                        {

                        } else
                        {
                            return $this->redirect(
                                $this->indexUrl
                            );
                        }

                        $model->refresh();

                    }
                }
            }
        }

        return $this->render('_form', [
            'model'     => $model,
            'handler'   => $handler,
        ]);
    }


    public function actionExport()
    {
        $rr = new RequestResponse();

        $model = new ExportTask();
        $model->loadDefaultValues();

        if ($post = \Yii::$app->request->post())
        {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler)
        {
            if ($post = \Yii::$app->request->post())
            {
                $handler->load($post);
            }
        } else
        {
            $rr->success = false;
            $rr->message = 'Компонент не настроен';
            return $rr;
        }

        $model->validate();
        $handler->validate();

        if (!$model->errors && !$handler->errors)
        {
            $rr->success = true;

            try
            {
                $result = $handler->export();

                $log = (string) $result;

                $rr->success = true;
                $rr->data = [
                    'html'           => <<<HTML
                    <br />
                    <br />
<div class="alert-success alert fade in">
Файл успешно сформирован: <a href="{$handler->file_path}" data-pjax="0" target="_blank">{$handler->file_path}</a><br />
</div>
<textarea class="form-control" rows="20" readonly>{$log}</textarea>
HTML
,
                ];
            } catch (\Exception $e)
            {
                $rr->success = false;
                $rr->message = $e->getMessage();
            }


        } else
        {
            $rr->success = false;
            $rr->message = 'Проверьте правильность указанных данных';
        }

        return $rr;
    }


}
