<?php
/**
 * Each engineer has a duty to keep the code elegant
 * Created by xiaobai at 2023/7/1 22:19
 */

namespace Cmslz\HyperfTenancy\Kernel\Migrations;


class MigrationCreator extends \Hyperf\Database\Migrations\MigrationCreator
{
    /**
     * Get the path to the stubs.
     */
    public function stubPath(): string
    {
        return __DIR__ . '/../../Commands/stubs';
    }

    /**
     * Get the migration stub file.
     */
    protected function getStub(?string $table, bool $create): string
    {
        if (is_null($table)) {
            return $this->files->get($this->stubPath() . '/blank.stub');
        }

        // We also have stubs for creating new tables and modifying existing tables
        // to save the developer some typing when they are creating a new tables
        // or modifying existing tables. We'll grab the appropriate stub here.
        $stub = $create ? 'create.stub' : 'update.stub';

        return $this->files->get($this->stubPath() . "/{$stub}");
    }

}