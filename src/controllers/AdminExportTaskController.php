<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 15.04.2016
 */

namespace skeeks\cms\export\controllers;

use skeeks\cms\agent\models\CmsAgentModel;
use skeeks\cms\backend\controllers\BackendModelStandartController;
use skeeks\cms\export\models\ExportTask;
use skeeks\cms\helpers\RequestResponse;
use skeeks\cms\modules\admin\actions\modelEditor\AdminModelEditorAction;
use skeeks\cms\rbac\CmsManager;
use yii\base\Event;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * Class AdminExportTaskController
 * @package skeeks\cms\export\controllers
 */
class AdminExportTaskController extends BackendModelStandartController
{
    public $notSubmitParam = 'sx-not-submit';

    public function init()
    {
        $this->name = \Yii::t('skeeks/export', 'Tasks on exports');
        $this->modelShowAttribute = "id";
        $this->modelClassName = ExportTask::className();

        $this->generateAccessActions = false;
        $this->permissionName = CmsManager::PERMISSION_ROLE_ADMIN_ACCESS;


        parent::init();
    }

    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [

            'index' => [
                'configKey' => $this->uniqueId.'/index/queue-v1',
                'on afterRender' => function (Event $e) {
                    $site_id = \Yii::$app->skeeks->site->id;
                    $e->content = \yii\bootstrap\Alert::widget([
                        'closeButton' => false,
                        'options'     => [
                            'class' => 'alert-default',
                        ],

                        'body' => <<<HTML
<p>Запуск экспорта через очередь из консоли:</p>
<p><b>CMS_SITE={$site_id} php yii cmsExport/execute/task id</b></p>
<p>Расписание настраивается в строке экспорта или в разделе «Расписание» → «Экспорт».</p>
<p>После запуска можно закрыть страницу: работу продолжит сервер.</p>
HTML
                        ,
                    ]);
                },

                'filters'         => false,
                'backendShowings' => false,
                'grid'            => [

                    'on init' => function (Event $e) {
                        /**
                         * @var $dataProvider ActiveDataProvider
                         * @var $query ActiveQuery
                         */
                        $query = $e->sender->dataProvider->query;

                        $query->andWhere(['cms_site_id' => \Yii::$app->skeeks->site->id]);
                    },

                    'visibleColumns' => [
                        'checkbox',
                        'actions',

                        'name',
                        'component',
                        'job',
                        'schedule',
                    ],
                    'columns'        => [
                        'job' => [
                            'label' => 'Запуск', 'format' => 'raw',
                            'value' => function (ExportTask $task) {
                                return \skeeks\cms\job\widgets\JobButton::widget([
                                    'startUrl' => ['start-job', 'id' => $task->id],
                                    'statusUrl' => ['job-status', 'id' => $task->id],
                                ]);
                            },
                        ],
                        'schedule' => [
                            'label' => 'Расписание', 'format' => 'raw',
                            'value' => function (ExportTask $task) { return $this->renderSchedule($task); },
                        ],
                        'name'      => [
                            'class' => \skeeks\cms\backend\grid\BackendEntityLinkColumn::class,
                            'controllerId' => '/cmsExport/admin-export-task',
                            'action' => 'update',
                            'attribute' => 'name',
                            'content' => function (ExportTask $task) {
                                $result = Html::tag('span', Html::encode($task->asText), ['class' => 'sx-collection-cell__primary']);

                                if ($task->description) {
                                    $result .= Html::tag('span', Html::encode($task->description), ['class' => 'sx-collection-cell__secondary']);
                                }

                                return $result;
                            },
                        ],
                        'component' => [
                            'format' => 'raw',
                            'value'  => function (ExportTask $task) {
                                $result = "";
                                if ($task->handler) {
                                    $result = $task->handler->name."<br />";
                                }
                                $result .= $task->component;

                                $handler = $task->handler;
                                if ($handler && strpos($handler->file_path, '/') === 0 && strpos($handler->file_path, '//') !== 0 && is_file($handler->rootFilePath)) {
                                    $result .= '<br>'.Html::a('Открыть файл экспорта', $handler->file_path, ['target' => '_blank', 'rel' => 'noopener', 'data-pjax' => '0']);
                                }

                                return $result;
                            },
                        ],
                    ],
                ],
            ],

            'create' => [
                'callback' => [$this, 'create'],
            ],

            'update' => [
                'callback' => [$this, 'update'],
            ],
        ]);
    }


    public function create()
    {
        $rr = new RequestResponse();

        $model = new ExportTask();
        $model->loadDefaultValues();

        if ($post = \Yii::$app->request->post()) {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler) {
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                $model->component_settings = $handler->toArray();
                if ($model->load(\Yii::$app->request->post()) && $handler->load(\Yii::$app->request->post())
                    && $model->validate() && $handler->validate()) {
                    $model->save();

                    \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Saved'));

                    return $this->redirect(
                        $this->url
                    );

                } else {
                    \Yii::$app->getSession()->setFlash('error', \Yii::t('app', 'Could not save'));
                }
            }
        }

        return $this->render('_form', [
            'model'   => $model,
            'handler' => $handler,
        ]);
    }


    public function update()
    {
        $rr = new RequestResponse();

        $model = $this->model;

        if ($post = \Yii::$app->request->post()) {
            $model->load($post);
        }

        $handler = $model->handler;
        if ($handler) {
            if ($post = \Yii::$app->request->post()) {
                $handler->load($post);
            }
        }

        if ($rr->isRequestPjaxPost()) {
            if (!\Yii::$app->request->post($this->notSubmitParam)) {
                if ($rr->isRequestPjaxPost()) {
                    $model->component_settings = $handler->toArray();

                    if ($model->load(\Yii::$app->request->post()) && $handler->load(\Yii::$app->request->post())
                        && $model->validate() && $handler->validate()) {
                        $model->save();

                        \Yii::$app->getSession()->setFlash('success', \Yii::t('app', 'Saved'));

                        if (\Yii::$app->request->post('submit-btn') == 'apply') {

                        } else {
                            /*return $this->redirect(
                                $this->indexUrl
                            );*/
                        }

                        $model->refresh();

                    }
                }
            }
        }

        return $this->render('_form', [
            'model'   => $model,
            'handler' => $handler,
        ]);
    }


    /** Retired synchronous endpoint: never execute submitted handler settings. */
    public function actionExport()
    {
        throw new \yii\web\GoneHttpException('Сохраните настройку и используйте запуск через очередь.');
    }

    protected function resolveExport($id)
    {
        if (\Yii::$app->user->isGuest || !\Yii::$app->user->can($this->permissionName)) {
            throw new \yii\web\ForbiddenHttpException();
        }
        $task = ExportTask::findOne(['id' => (int)$id, 'cms_site_id' => \Yii::$app->skeeks->site->id]);
        if (!$task) { throw new \yii\web\NotFoundHttpException(); }
        return $task;
    }

    public function actionStartJob($id)
    {
        if (!\Yii::$app->request->isPost) { throw new \yii\web\MethodNotAllowedHttpException(); }
        $task = $this->resolveExport($id);
        return $this->jobResponse($task->enqueue());
    }

    public function actionJobStatus($id)
    {
        $task = $this->resolveExport($id);
        return $this->jobResponse($task->getLatestJob(true) ?: $task->getLatestJob());
    }

    protected function jobResponse($run)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['success' => true, 'run' => $run ? [
            'id' => (int)$run->id, 'status' => $run->status, 'label' => $run->statusText,
            'finished' => $run->isFinished, 'percent' => $run->progressPercent,
            'message' => $run->isFinished
                ? ($run->error_message ?: (in_array($run->status, ['succeeded', 'succeeded_with_warnings'], true) ? 'Файл сформирован' : ''))
                : $run->progress_message,
            'url' => \yii\helpers\Url::to(['/cmsJob/admin-cms-job-run/view', 'pk' => $run->id]),
            'windowUrl' => \skeeks\cms\backend\helpers\BackendUrlHelper::createByParams([
                '/cmsJob/admin-cms-job-run/view', 'pk' => $run->id,
            ])->enableEmptyLayout()->enableNoActions()->url,
        ] : null];
    }

    public function renderSchedule(ExportTask $task)
    {
        $links = [];
        $agents = CmsAgentModel::find()->andWhere(['cms_site_id' => $task->cms_site_id])
            ->andWhere(['or', ['job_type' => 'export.execute'], ['name' => 'cmsExport/execute/task '.$task->id]])->all();
        foreach ($agents as $agent) {
            if ($agent->job_type === 'export.execute' && (int)($agent->jobPayload['export_task_id'] ?? 0) !== (int)$task->id) { continue; }
            $links[] = Html::a(($agent->is_active ? 'Включено' : 'Отключено').' — '.\Yii::$app->formatter->asDuration($agent->agent_interval),
                ['/cmsAgent/admin-cms-agent/update', 'pk' => $agent->id], ['data-pjax' => '0']);
        }
        $links[] = Html::a($links ? 'Добавить расписание' : 'Настроить расписание',
            ['/cmsAgent/admin-cms-agent/create', 'job_type' => 'export.execute', 'jobTargetId' => $task->id], ['data-pjax' => '0']);
        return implode('<br>', $links);
    }
}
