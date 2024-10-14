<?php

return [
    /** Default value when no file uploaded. */
    'default' => env('FILE_CAST_DEFAULT'),

    /** Default storage disk */
    'disk' => env('FILE_CAST_DISK'),

    /** Default storage folder. If NULL, the Model's table name will be used. */
    'folder' => env('FILE_CAST_FOLDER'),

    /** Automatically clean files on column value updated. */
    'auto_delete' => env('FILE_CAST_AUTO_DELETE', true),

    /** Serialize attribute to full URL. */
    'serialize_to_full_url' => env('FILE_CAST_SERIALIZE_TO_FULL_URL', true),
];
