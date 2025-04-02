<?php

return [
'temporary_file_upload' => [
    'rules' => ['file', 'max:20480'], // Max size is 20MB
    'disk' => null, // Use default disk
    'directory' => null, // Use default directory
    'middleware' => null, // No additional middleware
],
];