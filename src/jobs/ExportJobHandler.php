<?php
namespace skeeks\cms\export\jobs;

use skeeks\cms\export\models\ExportTask;
use skeeks\cms\job\contracts\JobReporterInterface;
use skeeks\cms\job\handlers\AbstractJobHandler;
use skeeks\cms\job\runtime\JobContext;

/** All entry points publish this operation; only the worker executes exports. */
class ExportJobHandler extends AbstractJobHandler
{
    const TYPE = 'export.execute';

    public static function key(array $payload, $run): string
    {
        return 'export:'.(int)$run->cms_site_id.':'.(int)($payload['export_task_id'] ?? 0);
    }

    public static function resource(array $payload, $run): string
    {
        $task = ExportTask::findOne(['id' => (int)($payload['export_task_id'] ?? 0), 'cms_site_id' => $run->cms_site_id]);
        if (!$task || !($handler = $task->handler)) {
            throw new \InvalidArgumentException('Настройка экспорта недоступна.');
        }
        $path = \yii\helpers\FileHelper::normalizePath($handler->rootFilePath);
        return 'export-file:'.hash('sha256', $path);
    }

    public function run(JobContext $context, JobReporterInterface $reporter): void
    {
        $task = ExportTask::findOne([
            'id' => (int)$context->get('export_task_id'),
            'cms_site_id' => $context->getRun()->cms_site_id,
        ]);
        if (!$task || !($handler = $task->handler)) {
            throw new \RuntimeException('Настройка экспорта или её обработчик недоступны.');
        }
        if (!$handler->validate()) {
            throw new \RuntimeException(implode('; ', $handler->getFirstErrors()));
        }
        if (self::resource($context->getPayload(), $context->getRun()) !== $context->getRun()->resource_key) {
            throw new \RuntimeException('Путь экспорта изменился после постановки в очередь. Запустите экспорт повторно.');
        }
        $reporter->setStage('export', mb_substr('Формирование файла: '.$task->name, 0, 255));
        $result = new ExportJobResult(['reporter' => $reporter]);
        $handler->setResult($result);
        $result->checkpoint();
        $handler->export();
        $result->checkpoint();
        if (!$result->success) {
            throw new \RuntimeException($result->message ?: 'Экспорт завершился с ошибкой.');
        }
        $reporter->setResult([
            'export_task_id' => (int)$task->id,
            'file_url' => $handler->file_path,
        ]);
        $reporter->setStage('complete', 'Файл сформирован');
        $reporter->info('Файл сформирован: '.$handler->file_path);
    }
}
