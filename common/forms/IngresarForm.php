<?php

namespace common\forms;

use yii\base\Model;

class IngresarForm extends Model
{
	public $usuario;
	public $clave;
	
	public function rules()
	{
		return [
			[['usuario', 'clave'], 'required'],
			[['usuario', 'clave'], 'string', 'max' => 50],
		];
	}
}