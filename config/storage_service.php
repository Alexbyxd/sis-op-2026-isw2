<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Police Storage Interoperability Configuration
    |--------------------------------------------------------------------------
    */

    'max_file_size_kb' => (int) env('MAX_FILE_SIZE_KB', 51200),

    'signed_url_expiration_minutes' => (int) env('SIGNED_URL_EXPIRATION_MINUTES', 30),

    'sistema1_password' => (string) env('SISTEMA1_PASSWORD', 'Correspondencia2026!'),

    'sistema2_password' => (string) env('SISTEMA2_PASSWORD', 'Oficiales2026!'),

];
