# Export execution through cms-job

The export list and legacy `cmsExport/execute/task ID` command enqueue
`export.execute` on the `exports` lane. The settings form only saves settings.
The retained ExportWidget API also queues saved tasks; it is no longer included
in the settings form. The old synchronous HTTP `export` endpoint returns 410:
submitted unsaved handler settings are never executed. The worker loads the
saved task scoped to the run's CMS site.

The common type owns task-and-site deduplication (skip) and an output-path
resource lock shared by different tasks writing the same file. The worker
rejects a changed output path after enqueue. Unknown custom handlers are not
declared idempotent: one attempt, no automatic replay after uncertain failure.
Manual retry uses cms-job. Other saved settings are read when execution begins.

## Handler reporting

ExportResult exposes checkpoint(), setTotal() and itemFinished(). Legacy
implementations keep no-op methods; ExportJobResult forwards to the buffered
reporter. Handlers should checkpoint during long work and propagate all
JobException subclasses; per-product failures use itemFinished(id, message).
Do not catch cancellation/fencing as an ordinary rejected product. Diagnostic
messages are bounded by cms-job; per-product IDs are not copied into events.

The Yandex Market handler reports every attempted product and publishes XML by
same-directory staging and rename, keeping the old feed until the new file is
complete. Its staging file is removed in finally on ordinary failure. Hard
process termination may leave an unreferenced `.export-*` file; no automatic
deletion of arbitrary output-directory files is introduced. The existing DOM
builder and product eligibility rules are retained. Other handler packages must
opt into progress checkpoints; do not claim item-level cancellation for them.

## Scheduling and deployment

cmsAgent.jobTargets registers ExportScheduleTarget for export.execute. Users
select a saved export in the schedule form; server validation generates
`{"export_task_id": ID}`. Existing export schedules remain visible in the export
row. The retained CLI command only enqueues, so old command schedules keep
working. To convert an existing row to native scheduling, resolve its task and
site, preserve ID, interval, dates and activation, and replace only job_type and
job_payload. Do not create a second active schedule for that same old row.

Deploy cms-agent's JobTargetProviderInterface and provider support together with
cms-export, then the Yandex adapter. Composer release requirements target
cms-agent 3.3 and cms-export 1.1; direct-file deployments are not published tags.
No schema migration is required. Back up current source before replacement,
drain workers and deploy new classes before the common registry configuration.
Run `cms-job/worker/queues --json=1` as the site Unix user and confirm exports.
Restart the dispatcher; an explicit --queues allowlist must include exports.
Hosting reconciliation owns subsequent service management.

`tests/queue-smoke.php` boots the local application and rolls back all fixture
rows and transport messages. It covers CLI enqueue, dedup across manual and
scheduled starts, saved target restoration, site validation, worker execution,
progress/error counters and cancellation. Run only on a local/test application.
