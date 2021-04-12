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
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
	'modules' => [],
    'controllerNamespace' => 'frontend\controllers',
    'components' => [        
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-frontend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'frontend',
        ],
    	'request' => [
    		'baseUrl' => '',
    		'csrfParam' => '_csrf-frontend',
    	],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
        	'enableStrictParsing' => true,
            'rules' => $urls,
        ],
    ],
    'params' => $params,
];
