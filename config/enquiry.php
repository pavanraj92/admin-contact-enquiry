<?php

return [
    'name' => 'Enquiry',
    /*
    |--------------------------------------------------------------------------
    | Enquiry Package Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the enquiry package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    */
    'pagination' => [
        'per_page' => env('ENQUIRY_PER_PAGE', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Options
    |--------------------------------------------------------------------------
    */
    'statuses' => [
        'new' => 'New',
        'draft' => 'Draft',
        'replied' => 'Replied',
        'closed' => 'Closed',
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    */
    'email' => [
        'from_address' => env('ENQUIRY_FROM_EMAIL', 'noreply@example.com'),
        'from_name' => env('ENQUIRY_FROM_NAME', 'Support Team'),
        'subject' => env('ENQUIRY_EMAIL_SUBJECT', 'Reply to your enquiry'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'admin_reply' => [
            'required',
            'string',
            'min:5',
            'max:2000',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'list' => 'enquiry_manager_list',
        'view' => 'enquiry_manager_view',
        'reply' => 'enquiry_manager_reply',
        'delete' => 'enquiry_manager_delete',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Settings
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => 'admin',
        'name_prefix' => 'admin.',
        'middleware' => ['web', 'admin.auth'],
    ],

    /*
    |--------------------------------------------------------------------------
    | View Settings
    |--------------------------------------------------------------------------
    */
    'views' => [
        'namespace' => 'enquiry',
        'admin_prefix' => 'admin',
    ],
];