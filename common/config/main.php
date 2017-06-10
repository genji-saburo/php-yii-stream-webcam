<?php

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'Yii2Twilio' => [
            'class' => 'filipajdacic\yiitwilio\YiiTwilio',
            //  Test credentials
            //'account_sid' => 'ACa723cf2a109c6b66c5bf8413189d68a1',
            //'auth_key' => '78247ebff9713a4292fd0493b0d50af7',
            //  Production credentials
            'account_sid' => 'AC261e13dc0c7b030dfb3806b28b10b3fc',
            'auth_key' => '90f28724592ffafd8b3bc05273300d2d',
            //  Production PatroleumApp keys
            //'account_sid' => 'SK100c7713bf59f8007596675a82c5e0d9',
            //'auth_key' => 'CLe4PabVt9hdRN1Ljhbb5oY0T7XW6iCS',
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            //'dateFormat' => 'dd/MM/Y',
            //'datetimeFormat' => 'dd/MM/Y H:m:ss',
            //'timeFormat' => 'H:i:s',
            'timeZone' => 'America/Los_Angeles',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
];
