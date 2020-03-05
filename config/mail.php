<?php
//if(YII_ENV != 'prod') {
//    return [
//        'class' => 'yii\swiftmailer\Mailer',
//        'transport' => [
//            'class' => 'Swift_SmtpTransport',
//            'host' => 'smtp.yandex.ru',
//            'username' => 'test-ncp@pepsico.digital',
//            'password' => 'mcmx2XnG',
//            'port' => '587',
//            'encryption' => 'tls',
//        ],
//        'messageConfig' => [
//            'charset' => 'UTF-8',
//            'from' => ['test-ncp@pepsico.digital' => 'PepsiCo ncp-test'],
//        ],
//    ];
////}
//
//return [
//    'class' => 'yii\swiftmailer\Mailer',
//    'transport' => [
//        'class' => 'Swift_SmtpTransport',
//        'host' => 'smtp.mailtrap.io',
//        'username' => '2c5ad0ad9e0875',
//        'password' => '0c6f7cda310b76',
//        'port' => '2525',
//        'encryption' => 'tls',
//    ],
//    'messageConfig' => [
//        'charset' => 'UTF-8',
//        'from' => ['ncp@pepsico.digital' => 'PepsiCo ncp'],
//    ],
//];
return [
    'class' => 'yii\swiftmailer\Mailer',
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'mail6.interaxions.ru',
        'username' => 'jira-icnx@interaxions.ru',
        'password' => 'VArYo0V8PU0M',
        'encryption' => '',
    ],
    'messageConfig' => [
        'charset' => 'UTF-8',
        'from' => ['jira-icnx@interaxions.ru' => 'jira-icnx'],
    ],
];
