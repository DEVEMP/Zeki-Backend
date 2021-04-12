<?php
return [
    'components' => [
    	'log' => new yii\helpers\ReplaceArrayValue([
    		'traceLevel' => YII_DEBUG ? 3 : 0,
    		'targets' => [
    			[
    				'class' => 'yii\log\FileTarget',
    				'levels' => ['error', 'warning'],
    			],
    		],
    	]),
    	'db' => new yii\helpers\ReplaceArrayValue([
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=chatbot_bd',
            'username' => 'root',
            'password' => '18757685',
            'charset' => 'utf8',
        ]),
    	'mailer' => [
    		'class' => 'yii\swiftmailer\Mailer',
    		'transport' => [
    			'class' => 'Swift_SmtpTransport',
    			'host' => 'pro.turbo-smtp.com',
    			'username' => 'victor@corefix.com.mx',
    			'password' => 'RazoF1dh',
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
