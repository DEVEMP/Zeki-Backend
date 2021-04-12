<?php

namespace common\forms;

use yii\base\Model;

class RecuperarClaveForm extends Model
{
	public $email;
	
	public function rules()
	{
		return [
			[['email'], 'required'],
			[['email'], 'email'],
		];
	}
}