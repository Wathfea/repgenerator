<?php

namespace Pentacom\Repgenerator\Domain\Migration\Writer;

use Illuminate\Support\Facades\File;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\ToStringInterface;

/**
 * Class MigrationWriter
 */
class MigrationWriter
{
    /**
     * @param  MigrationStub  $migrationStub
     */
    public function __construct(private MigrationStub $migrationStub)
    {
    }

    /**
     * Writes migration to destination.
     *
     * @param  string  $path  Migration file destination path.
     * @param  string  $stubPath  Migration stub file path.
     * @param  ToStringInterface  $up  migration up method
     * @param  ToStringInterface  $down  migration down method
     * @param  string  $nameKey
     */
    public function writeTo(string $path, string $stubPath, ToStringInterface $up, ToStringInterface $down, string $nameKey): void {
        $stub = $this->migrationStub->getStub($stubPath);


        $use = implode(Constants::LINE_BREAK, [
            'use Illuminate\Database\Migrations\Migration;',
            'use Illuminate\Database\Schema\Blueprint;',
            'use Illuminate\Support\Facades\Schema;',
            'use App\Domain\CrudMenu\Repositories\CrudMenuRepositoryService;',
            'use App\Domain\CrudMenu\Models\CrudMenu;'
        ]);

        File::put($path, $this->migrationStub->populateStub($stub, $use, $up->toString(), $down->toString(), $nameKey));

    }
}
