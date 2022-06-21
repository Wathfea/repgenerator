<?php

namespace Pentacom\Repgenerator\Domain\Migration;

use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\SchemaBlueprint;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\TableBlueprint;
use Pentacom\Repgenerator\Domain\Migration\Writer\MigrationWriter;

/**
 * Class MigrationGeneratorService
 */
class MigrationGeneratorService
{
    /* @var  MigrationSettings $settings */
    public MigrationSettings $settings;

    /**
     * @param  MigrationWriter  $migrationWriter
     * @param  ColumnGenerator  $columnGenerator
     * @param  IndexGenerator  $indexGenerator
     * @param  ForeignGenerator  $foreignGenerator
     */
    public function __construct(
        private MigrationWriter $migrationWriter,
        private ColumnGenerator $columnGenerator,
        private IndexGenerator $indexGenerator,
        private ForeignGenerator $foreignGenerator
    )
    {
    }

    /**
     * @param  null  $path
     * @param  null  $date
     * @param  null  $fileName
     */
    public function setup($path = null, $date = null, $fileName = null) {
        $this->settings = app(MigrationSettings::class);
        $this->settings->setPath( $path ?? Config::get('repgenerator.migration_target_path'));

        $this->settings->setDate(Carbon::parse($date ?? Carbon::now()));
        $this->settings->setTableFilename(Config::get('repgenerator.filename_pattern.table'));
        $this->settings->setForeignKeyFilename(Config::get('repgenerator.filename_pattern.foreign_key'));
        $this->settings->setStubPath(Config::get('repgenerator.migration_stub_path'));
    }


    /**
     * @param  Table  $table
     * @param  array  $columns
     * @param  array  $indexes
     * @param  array  $foreigns
     */
    public function generateMigrationFiles(Table $table, array $columns, array $indexes, array $foreigns) {
        $up   = $this->up($table, $columns, $indexes, $foreigns);
        $down = $this->down($table, $foreigns);


        $this->migrationWriter->writeTo(
            $this->makeTablePath($table->getName()),
            $this->settings->getStubPath(),
            $up,
            $down
        );
    }

    /**
     * Generates `up` schema for table.
     */
    public function up(Table $table, array $columns, array $indexes, array $foreigns): SchemaBlueprint
    {
        $up = $this->getSchemaBlueprint($table, 'create');

        $tableBlueprint = new TableBlueprint();

        foreach ($columns as $column) {
            $method = $this->columnGenerator->generate($table, $column->toArray());
            $tableBlueprint->setMethod($method);
        }

        if(!empty($indexes)) {
            foreach ($indexes as $index) {

                $indexName = null;
                if(count($index) > 1) {
                    //Composite index creation, we should check index name length
                    $indexName = $table->getName().'_'.implode('_',$index['columns']).'_'.$index['type'];
                    if(Str::length($indexName) > 32) {
                        //Generate short index name
                        $columns = array_map(function($val) { return Str::limit($val,2, ''); }, $index['columns']);
                        $indexName = Str::limit($table->getName(),2, '').'_'.implode('_',$columns).'_'.$index['type'];
                    }
                }

                $method = $this->indexGenerator->generate($index);

                if($indexName) {
                    $method->setSecondParameter($indexName);
                }

                $tableBlueprint->setMethod($method);
            }
        }

        if(!empty($foreigns)) {
            foreach ($foreigns as $foreign) {
                $method = $this->foreignGenerator->generate($foreign);
                $tableBlueprint->setMethod($method);
            }
        }

        $up->setBlueprint($tableBlueprint);

        return $up;
    }

    /**
     * Generates `down` schema for table.
     *
     * @param Table $table
     * @param array $foreigns
     * @return SchemaBlueprint
     */
    public function down(Table $table, array $foreigns): SchemaBlueprint
    {
        $down = $this->getSchemaBlueprint($table, 'dropIfExists');
        $downBlueprint = new TableBlueprint();

        foreach ($foreigns as $foreign) {
            $method = $this->foreignGenerator->generateDrop($foreign, $table);
            $downBlueprint->setMethod($method);
        }

        $down->setBlueprint($downBlueprint);

        return $down;
    }


    /**
     * @param  Table  $table
     * @param  string  $schemaBuilder
     * @return SchemaBlueprint
     */
    private function getSchemaBlueprint(Table $table, string $schemaBuilder): SchemaBlueprint
    {
        return new SchemaBlueprint(
            $table->getName(),
            $schemaBuilder
        );
    }

    /**
     * Makes file path for table migration.
     *
     * @param  string  $table  Table name.
     * @return string
     */
    private function makeTablePath(string $table): string
    {
        return $this->makeFilename(
            $this->settings->getTableFilename(),
            $this->settings->getDate()->format('Y_m_d_His'),
            $table
        );
    }

    /**
     * Makes migration filename by given naming pattern.
     *
     * @param  string  $pattern  Naming pattern for migration filename.
     * @param  string  $datetimePrefix  Current datetime for filename prefix.
     * @param  string  $table  Table name.
     * @return string
     */
    private function makeFilename(string $pattern, string $datetimePrefix, string $table): string
    {
        $path     = $this->settings->getPath();
        $filename = $pattern;
        $replace  = [
            '[datetime_prefix]' => $datetimePrefix,
            '[table]'           => $table,
        ];
        $filename = str_replace(array_keys($replace), $replace, $filename);
        return "$path/$filename";
    }
}
