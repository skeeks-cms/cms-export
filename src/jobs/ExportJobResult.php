<?php
namespace skeeks\cms\export\jobs;

use skeeks\cms\export\helpers\ExportResult;
use skeeks\cms\job\exceptions\JobCancelledException;

/** Streams diagnostics instead of accumulating the complete export log in RAM. */
class ExportJobResult extends ExportResult
{
    public $reporter;

    public function checkpoint()
    {
        $this->reporter->heartbeat();
        if ($this->reporter->isCancelled()) {
            throw new JobCancelledException('Экспорт отменён.');
        }
    }

    public function stdout($message, $int = 0)
    {
        $this->checkpoint();
        // Legacy handlers log each product; retain only bounded stage diagnostics.
        if (!preg_match('/^\s*\d+\s/', $message)) {
            $this->reporter->info(trim($message));
        }
        return $this;
    }

    public function setTotal($total)
    {
        $this->reporter->setTotal((int)$total);
    }

    public function itemFinished($id, $error = null)
    {
        if ($error !== null) {
            $this->reporter->itemError('product', $id, $error);
        } else {
            $this->reporter->countSuccess();
        }
        $this->reporter->advance();
        $this->checkpoint();
    }

    public function itemSkipped($id, $reason)
    {
        $this->reporter->countSkipped();
        $this->reporter->warning('Товар '.$id.' пропущен: '.$reason, ['product_id' => $id]);
        $this->reporter->advance();
        $this->checkpoint();
    }
}
