<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Helpers;


final class CharacterCounterStore
{

    /**
     * @var int $charsCount
     */
    public static int $charsCount = 0;

    public static function addFileCharacterCount($file)
    {
        $count = 0;

        $fh = fopen($file, 'r');
        while (!feof($fh)) {
            $fr = fread($fh, 8192);
            $count += strlen($fr);
        }
        fclose($fh);

        self::$charsCount += $count;
    }
}
