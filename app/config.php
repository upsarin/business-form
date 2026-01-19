<?php
return [
    'app' => [
        'site_path' => '', // эта настройка скорее для подсайтов
    ],

    'recaptcha' => [
        'site_key' => '', // ключ
        'secret_key' => '', // секретный ключ
    ],

    'smtp' => array(
        'host' => 'smtp.yandex.ru',
        'port' => 465,
        'secure' => 'ssl',
        'username' => '', // логин почты
        'password' => '', // пароль приложения
        'from_email' => '', // адрес отправителя
        'from_name' => '', // имя отправителя

        // КУДА отправлять заявки
        'to_email' => '', // адрес получателя
        'to_name' => '', // имя получателя
    ),

    'telegram' => [
        'bot_token' => '', // bot token
        'chat_id' => '-', // chat id
    ],

    'limits' => [
        'max_file_size' => 10 * 1024 * 1024, // 10mb
        'allowed_ext' => ['pdf','doc','docx','rtf'],
        'allowed_mime' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/rtf',
            'text/rtf',
            'application/octet-stream'
        ],
    ],

    'security' => [
        'rate_limit_per_10min' => 8,
    ],
];
