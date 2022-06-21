<?php

namespace Pentacom\Repgenerator\MigrationGenerator;

use Carbon\Carbon;

/**
 * Class MigrationSettings
 */
class MigrationSettings
{
    /** @var string */
    private $path;

    /** @var Carbon */
    private $date;

    /** @var string */
    private $tableFilename;

    /** @var string */
    private $foreignKeyFilename;


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
    public function getStubPath(): string
    {
        return $this->stubPath;
    }

    /**
     * @param  string  $stubPath
     */
    public function setStubPath(string $stubPath): void
    {
        $this->stubPath = $stubPath;
    }
}
