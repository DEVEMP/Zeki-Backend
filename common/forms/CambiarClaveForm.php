<?php

namespace common\forms;

use yii\base\Model;

class CambiarClaveForm extends Model
{
	public $clave;
	
	public function rules()
	{
		return [
			[['clave'], 'required'],
			[['clave'], 'string', 'max' => 60],
		];
	}
}