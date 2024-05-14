<?php

return [
    /** Default storage disk */
    'disk' => env('FILE_CAST_DISK', 'public'),

    /**
     * Default storage folder. If NULL, the Model table name will be used.
     */
    'folder' => env('FILE_CAST_FOLDER', NULL),

    /** Automatically delete old files */
    'auto_delete' => env('FILE_CAST_AUTO_DELETE', true),
];
