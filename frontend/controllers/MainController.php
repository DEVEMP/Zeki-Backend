<?php

namespace frontend\controllers;

use Yii;
use yii\web\Response;
use common\controllers\CController;

class MainController extends CController
{
	public function actionIndex()
	{		
		$json_respuesta = ['message' => 'Hi from chatbot server'];
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
}
