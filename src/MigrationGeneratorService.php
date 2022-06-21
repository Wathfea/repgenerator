<?php

namespace Pentacom\Repgenerator;

/**
 * Class MigrationGeneratorService
 */
class MigrationGeneratorService
{
    /**
     * @return string[]
     */
    public static function getColumnTypes(): array {
        return [
            'id',
            'integer',
            'string',
            'text',
            'boolean',
            'bigIncrements',
            'bigInteger',
            'binary',
            'char',
            'dateTimeTz',
            'dateTime',
            'date',
            'decimal',
            'double',
            'enum',
            'float',
            'foreignId',
            'foreignIdFor',
            'foreignUuid',
            'geometryCollection',
            'geometry',
            'increments',
            'ipAddress',
            'json',
            'jsonb',
            'lineString',
            'longText',
            'macAddress',
            'mediumIncrements',
            'mediumInteger',
            'mediumText',
            'morphs',
            'multiLineString',
            'multiPoint',
            'multiPolygon',
            'nullableTimestamps',
            'nullableMorphs',
            'nullableUuidMorphs',
            'point',
            'polygon',
            'rememberToken',
            'set',
            'smallIncrements',
            'smallInteger',
            'softDeletesTz',
            'softDeletes',
            'timeTz',
            'time',
            'timestampTz',
            'timestamp',
            'timestampsTz',
            'timestamps',
            'tinyIncrements',
            'tinyInteger',
            'tinyText',
            'unsignedBigInteger',
            'unsignedDecimal',
            'unsignedInteger',
            'unsignedMediumInteger',
            'unsignedSmallInteger',
            'unsignedTinyInteger',
            'uuidMorphs',
            'uuid',
            'year'
        ];
    }
}
