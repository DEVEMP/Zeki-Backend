<?php
return yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/main.php',
    require __DIR__ . '/main-local.php',
    require __DIR__ . '/test.php',
    [
        'components' => [
        	'db' => [
        		'class' => 'yii\db\Connection',
        		'dsn' => 'mysql:host=localhost;dbname=revistatouch',
        		'username' => 'root',
        		'password' => '18757685',
        		'charset' => 'utf8',
        	],
        	'mailer' => [
        		'class' => 'yii\swiftmailer\Mailer',
        		'viewPath' => '@common/mail',
        		// send all mails to a file by default. You have to set
        		// 'useFileTransport' to false and configure a transport
        		// for the mailer to send real emails.
        		'useFileTransport' => true,
        	],
        ],
    ]
);
