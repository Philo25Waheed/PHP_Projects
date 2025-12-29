<?php
// Basic configuration - update these for your environment
return [
    'db' => [
        'host' => '127.0.0.1',
        'dbname' => 'meister_todo',
        'user' => 'root',
        'pass' => ''
    ],
    'site' => [
        'base_url' => '/Meister Company/ToDoList/public',
        'company_name' => 'Meister Company'
    ],
    'mail' => [
        'from_email' => 'noreply@company.local',
        'from_name' => 'Meister ToDo'
    ]
    , 'setup_key' => '' // set a secret key to protect public/setup.php in development, e.g. 'local-dev-key'
    , 'twilio' => [
        'account_sid' => '',
        'auth_token' => '',
        'from_whatsapp_number' => '' // e.g. 'whatsapp:+1415xxxxxxx' (use Twilio sandbox or registered number)
    ]
];
