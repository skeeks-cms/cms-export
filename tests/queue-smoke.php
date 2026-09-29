<?php
// Local integration test. All DB changes (including transport messages) roll back.
$root = getenv('SKEEKS_APP_ROOT') ?: '/app';
define('ROOT_DIR', $root);
define('YII_ENV', 'dev');
define('YII_DEBUG', true);
require $root.'/vendor/skeeks/cms/bootstrap.php';
$config = new \Yiisoft\Config\Config(new \Yiisoft\Config\ConfigPaths($root, 'config'), null,
    [\Yiisoft\Config\Modifier\RecursiveMerge::groups('console', 'console-'.ENV, 'params', 'params-console-'.ENV)], 'params-console-'.ENV);
$app = new \yii\console\Application($config->has('console-'.ENV) ? $config->get('console-'.ENV) : $config->get('console'));
function check($ok, $message) { if (!$ok) { throw new \RuntimeException($message); } echo "OK $message\n"; }
class ExportSmokeHandler extends \skeeks\cms\export\ExportHandler { public static $calls = 0; public function export() { self::$calls++; return $this->result; } }
class ExportSmokeReporter implements \skeeks\cms\job\contracts\JobReporterInterface
{
    public $cancelled = false, $processed = 0, $errors = 0, $success = 0, $total, $result;
    public function setStage(string $stage, ?string $message = null): void {}
    public function setTotal(?int $total): void { $this->total = $total; }
    public function advance(int $by = 1): void { $this->processed += $by; }
    public function countSuccess(int $by = 1): void { $this->success += $by; }
    public function countWarning(int $by = 1): void {}
    public function countError(int $by = 1): void { $this->errors += $by; }
    public function countSkipped(int $by = 1): void {}
    public function info(string $message, array $context = []): void {}
    public function warning(string $message, array $context = []): void {}
    public function error(string $message, array $context = []): void {}
    public function itemError(string $itemType, $itemId, string $message, array $row = []): void { $this->errors++; }
    public function heartbeat(): void {}
    public function isCancelled(): bool { return $this->cancelled; }
    public function addArtifact(string $type, string $path, array $options = []): \skeeks\cms\job\models\CmsJobRunArtifact { throw new \LogicException(); }
    public function setResult(array $result): void { $this->result = $result; }
}
$app->cmsExport->handlers = ['smoke' => ['class' => ExportSmokeHandler::class]];
$tx = $app->db->beginTransaction();
try {
    $task = new \skeeks\cms\export\models\ExportTask();
    $task->name = 'Queue smoke'; $task->component = 'smoke'; $task->component_settings = [];
    $task->cms_site_id = $app->skeeks->site->id;
    check($task->save(), 'fixture saved');
    check($app->runAction('cmsExport/execute/task', [$task->id]) === 0, 'legacy CLI succeeds');
    $run = $task->getLatestJob(true);
    check($run && $run->status === 'queued' && $run->queue_name === 'exports', 'manual start queues export');
    check(ExportSmokeHandler::$calls === 0, 'producer never executes handler');
    check((int)$task->enqueue()->id === (int)$run->id, 'duplicate manual start reuses active run');
    $agent = new \skeeks\cms\agent\models\CmsAgentModel();
    $agent->name = 'Smoke schedule'; $agent->executionMode = 'job'; $agent->job_type = 'export.execute';
    $agent->cms_site_id = $task->cms_site_id; $agent->jobTargetId = (string)$task->id;
    check($agent->validate(), 'schedule target validates');
    check($agent->jobPayload === ['export_task_id' => (int)$task->id], 'target builds canonical payload');
    check($agent->save(), 'native schedule saves');
    $saved = \skeeks\cms\agent\models\CmsAgentModel::findOne($agent->id);
    check($saved->jobTargetId === (string)$task->id, 'saved schedule restores selection');
    check($agent->jobDedupKey === $run->dedup_key, 'manual and scheduler share dedup');
    check($agent->pushJob() === null, 'schedule cannot duplicate active manual export');
    $agent->jobTargetId = '999999999';
    check(!$agent->validate() && $agent->hasErrors('jobTargetId'), 'invalid target rejected');
    $provider = new \skeeks\cms\export\jobs\ExportScheduleTarget();
    try { $provider->payload((string)$task->id, 999999999); throw new \RuntimeException('Cross-site accepted'); }
    catch (\InvalidArgumentException $expected) { echo "OK foreign-site target rejected\n"; }
    check($provider->selected(['export_task_id' => (int)$task->id]) === (string)$task->id, 'edit restores target');
    $handler = $app->jobs->getRegistry()->get('export.execute')->createHandler();
    $reporter = new ExportSmokeReporter();
    $context = new \skeeks\cms\job\runtime\JobContext(['run' => $run]);
    $handler->run($context, $reporter);
    check(ExportSmokeHandler::$calls === 1 && $reporter->result['export_task_id'] === (int)$task->id, 'worker executes configured export and reports result');
    $result = new \skeeks\cms\export\jobs\ExportJobResult(['reporter' => $reporter]);
    $result->setTotal(2); $result->itemFinished(1); $result->itemFinished(2, 'No price');
    check($reporter->total === 2 && $reporter->processed === 2 && $reporter->success === 1 && $reporter->errors === 1, 'progress counts successes and item errors');
    $reporter->cancelled = true;
    try { $handler->run($context, $reporter); throw new \RuntimeException('Cancellation ignored'); }
    catch (\skeeks\cms\job\exceptions\JobCancelledException $expected) { check(ExportSmokeHandler::$calls === 1, 'cancelled job never executes export'); }
    $run->updateAttributes(['status' => 'succeeded', 'dedup_active' => null]);
    $scheduledRun = $saved->pushJob();
    check($scheduledRun && $scheduledRun->trigger_type === 'schedule' && $scheduledRun->trigger_ref === 'cms_agent:'.$saved->id, 'native schedule publishes export with its own history');
} finally { $tx->rollBack(); }
echo "PASS\n";
