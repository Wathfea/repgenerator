<?php

namespace Pentacom\Repgenerator\Domain\Migration;

use Carbon\Carbon;

/**
 * Class MigrationSettings
 */
class MigrationSettings
{
    /** @var string */
    private string $path;

    /** @var Carbon */
    private Carbon $date;

    /** @var string */
    private string $tableFilename;

    /** @var string */
    private string $foreignKeyFilename;

    /**
     * @return Carbon
     */
    public function getDate(): Carbon
    {
        return $this->date;
    }

    /**
     * @param  Carbon  $date
     */
    public function setDate(Carbon $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getForeignKeyFilename(): string
    {
        return $this->foreignKeyFilename;
    }

    /**
     * @param  string  $foreignKeyFilename
     */
    public function setForeignKeyFilename(string $foreignKeyFilename): void
    {
        $this->foreignKeyFilename = $foreignKeyFilename;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param  string  $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getStubPath(): string
    {
        return $this->stubPath;
    }

    /**
     * @return string
     */
    public function getTableFilename(): string
    {
        return $this->tableFilename;
    }

    /**
     * @param  string  $tableFilename
     */
    public function setTableFilename(string $tableFilename): void
    {
        $this->tableFilename = $tableFilename;
    }

    /**
     * @param  string  $stubPath
     */
    public function setStubPath(string $stubPath): void
    {
        $this->stubPath = $stubPath;
    }
}
