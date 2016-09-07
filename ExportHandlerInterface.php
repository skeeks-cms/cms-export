<?php
/**
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010 SkeekS (СкикС)
 * @date 07.09.2016
 */
namespace skeeks\cms\export;
use skeeks\cms\export\helpers\ExportResult;
/**
 * @property string $id;
 * @property string $name;
 *
 * Interface ExportHandlerInterface
 * @package skeeks\cms\export
 */
interface ExportHandlerInterface
{
    /**
     * @return ExportResult
     */
    public function export();
}