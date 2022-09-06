<?php

namespace Pentacom\Repgenerator\Domain\Migration\Writer;

use Illuminate\Support\Facades\File;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\ToStringInterface;
use Pentacom\Repgenerator\Helpers\Constants;

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
     * @param string $path Migration file destination path.
     * @param string $stubPath Migration stub file path.
     * @param string $menuCodeStubPath
     * @param ToStringInterface $up migration up method
     * @param ToStringInterface $down migration down method
     * @param string $name
     * @param string $url
     * @param string $iconName
     * @param int|null $menuGroupId
     */
    public function writeTo(
        string $path,
        string $stubPath,
        string $menuCodeStubPath,
        ToStringInterface $up,
        ToStringInterface $down,
        string $name,
        string $url,
        string $iconName,
        int|null $menuGroupId = null,
        string|null $newMenuGroupName = null,
        string|null $newMenuGroupIcon = null
    ): void {
        $stub = $this->migrationStub->getStub($stubPath);
        $menuCodeStub = $this->migrationStub->getStub($menuCodeStubPath);


        $use = implode(Constants::LINE_BREAK, [
            'use Illuminate\Database\Migrations\Migration;',
            'use Illuminate\Database\Schema\Blueprint;',
            'use Illuminate\Support\Facades\Schema;',
            'use App\Domain\CrudMenu\Services\CrudMenuService;'
        ]);


        File::put($path, $this->migrationStub->populateStub($stub, $menuCodeStub, $use, $up->toString(), $down->toString(), $name, $url, $iconName, $menuGroupId, $newMenuGroupName, $newMenuGroupIcon));

    }
}
