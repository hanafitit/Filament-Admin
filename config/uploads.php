<?php

return [
    'order_attachments' => [
        'max_size_kb' => env('ORDER_ATTACHMENT_MAX_SIZE_KB', 50 * 1024),
        'accepted_file_types' => [
            'application/pdf',
            'application/zip',
            'application/msword',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png',
            'text/plain',
        ],
        'accepted_extensions' => [
            'pdf',
            'zip',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'jpg',
            'jpeg',
            'png',
            'txt',
        ],
    ],
];
