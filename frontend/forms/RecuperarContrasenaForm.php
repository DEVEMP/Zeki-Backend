<?php

namespace frontend\forms;

use yii\base\Model;

class RecuperarContrasenaForm extends Model
{
	public $email;
	
	public function rules()
	{
		return [
			[['email'], 'required'],
			[['email'], 'string', 'max' => 100],
		];
	}
	
	public function attributeLabels()
	{
		return [
			'email' => 'Correo Electrónico',
		];
	}
}