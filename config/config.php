<?php

/*
 * The main configurations for the generator
 */
return [
    // Migration stub file location.
    'migration_stub_path' => __DIR__.'/../src/resources/stubs/Migration.stub',

    // Where the generated files will be saved.
    'migration_target_path' => base_path('database/migrations'),

    // Migration filename pattern.
    'filename_pattern' => [
        'table' => '[datetime_prefix]_create_[table]_table.php',
        'foreign_key' => '[datetime_prefix]_add_foreign_keys_to_[table]_table.php',
    ],
];
