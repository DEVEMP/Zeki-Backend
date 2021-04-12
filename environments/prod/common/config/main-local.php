<?php
return [
    'components' => [
    	'log' => [
			'traceLevel' => YII_DEBUG ? 3 : 0,
			'targets' => [
				[
					'class' => 'yii\log\FileTarget',
					'levels' => ['error', 'warning'],
				],
			],
		],
    	'db' => new yii\helpers\ReplaceArrayValue([
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=chatbot_bd',
            'username' => 'root',
            'password' => '15188100vga',
            'charset' => 'utf8',
        ]),
    	'mailer' => [
    		'class' => 'yii\swiftmailer\Mailer',
    		'transport' => [
    			'class' => 'Swift_SmtpTransport',
    			'host' => 'pro.turbo-smtp.com',
    			'username' => 'info@elmundoplay.com',
    			'password' => 'J89X5THX',
    			'port' => '465',
    			'encryption' => 'ssl',
    			'timeout' => 5000,
    			'streamOptions' => [
    				'ssl' => [
    					'verify_peer' => false,
    					'verify_peer_name' => false,
    				],
    			],
    		],
    	],
    ],
];
