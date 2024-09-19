<?php

return [
    /** Default value when null */
    'default' => env('FILE_CAST_DEFAULT'),

    /** Default storage disk */
    'disk' => env('FILE_CAST_DISK', 'public'),

    /**
     * Default storage folder. If NULL, the Model table name will be used.
     */
    'folder' => env('FILE_CAST_FOLDER'),

    /** Automatically delete old files */
    'auto_delete' => env('FILE_CAST_AUTO_DELETE', true),
];
