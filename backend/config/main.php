<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

$urls = array_merge(
	require __DIR__ . '/urls.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'backend',
        ],
    	'request' => [
    		'baseUrl' => '/admin',
    		'csrfParam' => '_csrf-backend',
    	],
        'urlManager' => [
        	'enablePrettyUrl' => true,
        	'showScriptName' => false,
        	'enableStrictParsing' => true,
            'rules' => $urls
        ],
    ],
    'params' => $params,
];
