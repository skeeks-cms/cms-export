<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 30.08.2016
 */
/* @var $this yii\web\View */
/* @var $widget \skeeks\cms\export\widgets\ExportWidget */

$widget = $this->context;
$this->registerCss(<<<CSS

CSS
);
?>
<?= \yii\helpers\Html::beginTag('div', $widget->options); ?>
    <? if ($widget->showButton) : ?>
        <div style="text-align: center">
            <?= \yii\helpers\Html::button('Запустить экспорт', $widget->buttonOptions); ?>
        </div>
    <? endif; ?>

    <div class="sx-result-wrapper">

    </div>
<?= \yii\helpers\Html::endTag('div'); ?>

<?
$js = \yii\helpers\Json::encode($widget->clientOptions);

$this->registerJs(<<<JS
(function(sx, $, _)
{
    sx.classes.Export = sx.classes.Component.extend({

        _init: function()
        {

        },

        _onDomReady: function()
        {
            var self = this;

            this.jWidget = $("#" + this.get('id'));
            this.jBtnStart = $(".sx-start-btn", this.jWidget);
            this.jResultWrapper = $(".sx-result-wrapper", this.jWidget);

            this.jBtnStart.bind('click', function()
            {
                self.run();
                return false;
            });
        },

        /**
        *
        * @returns {sx.classes.Export}
        */
        run: function()
        {
            var self = this;

            this.trigger('run');
            this.jResultWrapper.empty();

            var AjaxQuery = sx.ajax.preparePostQuery(this.get('backend'), $('#' + this.get('formId')).serialize() );

            var AjaxHandler = new sx.classes.AjaxHandlerStandartRespose(AjaxQuery, {
                'allowResponseSuccessMessage' : false
            });

            AjaxHandler.bind('success', function(e, response)
            {
                self.jResultWrapper.empty().append(response.data.html);
            });

            AjaxHandler.bind('error', function(e, response)
            {
                console.log('Ошибка');
                console.log(e);
                console.log(response.data);
            });

            AjaxQuery.execute();

            //
            return this;
        },
    });

    sx.Export = new sx.classes.Export({$js});
})(sx, sx.$, sx._);
JS
);
?>
