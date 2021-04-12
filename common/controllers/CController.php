<?php
namespace common\controllers;

use Yii;
use yii\web\Controller;

class CController extends Controller
{
	public $layout = false;
	
	public function beforeAction($event)
	{
		return parent::beforeAction($event);
	}
}
