<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 31.03.2016
 */
/* @var $this yii\web\View */


?>

<? $pjax = \skeeks\cms\modules\admin\widgets\Pjax::begin(); ?>

    <?php echo $this->render('_search', [
        'searchModel'   => $searchModel,
        'dataProvider'  => $dataProvider
    ]); ?>

    <?= \skeeks\cms\modules\admin\widgets\GridViewStandart::widget([
        'dataProvider'      => $dataProvider,
        'filterModel'       => $searchModel,
        'pjax'              => $pjax,
        'adminController'   => $controller,
        'columns' =>
        [
            'id',
            'name',
            [
                'class' => \yii\grid\DataColumn::className(),
                'attribute' => 'component',
                'filter' => \yii\helpers\ArrayHelper::map(\Yii::$app->cmsExport->handlers, 'id', 'name'),
                'value'  => function(\skeeks\cms\export\models\ExportTask $exportTask)
                {
                    return $exportTask->handler->name;
                }
            ]
        ]

    ]); ?>

<? $pjax::end() ?>
