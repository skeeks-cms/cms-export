<?php
namespace skeeks\cms\export\jobs;

use skeeks\cms\agent\JobTargetProviderInterface;
use skeeks\cms\export\models\ExportTask;

class ExportScheduleTarget implements JobTargetProviderInterface
{
    public function label(): string { return 'Настройка экспорта'; }

    public function items(int $siteId): array
    {
        $items = [];
        foreach (ExportTask::find()->select(['id', 'name'])->andWhere(['cms_site_id' => $siteId])->orderBy(['name' => SORT_ASC])->asArray()->all() as $row) {
            $items[$row['id']] = ($row['name'] ?: 'Экспорт').' — #'.$row['id'];
        }
        return $items;
    }

    public function selected(array $payload): ?string
    {
        return isset($payload['export_task_id']) ? (string)$payload['export_task_id'] : null;
    }

    public function payload(string $id, int $siteId): array
    {
        if (!ctype_digit($id) || !ExportTask::find()->andWhere(['id' => (int)$id, 'cms_site_id' => $siteId])->exists()) {
            throw new \InvalidArgumentException('Выберите существующую настройку экспорта этого сайта.');
        }
        return ['export_task_id' => (int)$id];
    }
}
