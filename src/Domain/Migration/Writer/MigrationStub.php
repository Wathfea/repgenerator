<?php

namespace Pentacom\Repgenerator\Domain\Migration\Writer;

use Illuminate\Support\Facades\File;

/**
 * Class MigrationStub
 */
class MigrationStub
{
    /**
     * Get the migration stub file.
     *
     * @param  string  $stubPath
     * @return string File content.
     */
    public function getStub(string $stubPath): string
    {
        return File::get($stubPath);
    }


    /**
     * Populates the place-holders in the migration stub.
     *
     * @param string $stub File content.
     * @param string $migrationMenuStub
     * @param string $use
     * @param string $upContent Content for migration `up`.
     * @param string $downContent Content for migration `down`.
     * @param string $name The name of the menu
     * @param string $url The url at which the menu is found
     * @param string $iconName
     * @param int|null $menuGroupId
     * @param string|null $newMenuGroupName
     * @param string|null $newMenuGroupIcon
     * @return string Migration content.
     */
    public function populateStub(
        string $stub,
        string $migrationMenuStub,
        string $use,
        string $upContent,
        string $downContent,
        string $name,
        string $url,
        string $iconName,
        int|null $menuGroupId = null,
        string|null $newMenuGroupName = null,
        string|null $newMenuGroupIcon = null
    ): string {
        $migrationMenuCode = '';
        if ( !empty($menuGroupId) || ( !empty($newMenuGroupName) && !empty($newMenuGroupIcon) ) ) {
            $codeReplace =  [
                '{{ url }}' => $url,
                '{{ icon }}' => $iconName,
                '{{ name }}' => $name,
                '{{ crudMenuGroupId }}' => !empty($menuGroupId) ? $menuGroupId : 'null',
                '{{ newMenuGroupName }}' => $newMenuGroupName,
                '{{ newMenuGroupIcon }}' => $newMenuGroupIcon,
            ];
            $migrationMenuCode = str_replace(array_keys($codeReplace), $codeReplace, $migrationMenuStub);
        }
        $replace = [
            '{{ use }}' => $use,
            '{{ up }}' => $upContent,
            '{{ down }}' => $downContent,
            '{{ migrationMenuCode }}' => $migrationMenuCode,
        ];
        return str_replace(array_keys($replace), $replace, $stub);
    }
}
