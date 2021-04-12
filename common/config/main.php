<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
	'language'=>'es',
	'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
   		'view' => [
			'defaultExtension' => 'html',
			'class' => 'yii\web\View',
			'renderers' => [
				'html' => [
					'class' => 'yii\twig\ViewRenderer',
					'cachePath' => '@runtime/Twig/cache',
					'options' => [
						'auto_reload' => true,
					],
					'globals' => [
						'html' => ['class' => '\yii\helpers\Html'],
					],
					'uses' => ['yii\bootstrap'],
				],
			],
  		],
    ],
];
