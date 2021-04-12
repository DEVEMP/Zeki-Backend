<?php

namespace frontend\forms;

use yii\base\Model;

class RegistrarCuentaForm extends Model
{
	public $nombre;
	public $apellido;
	public $edad;
	public $email;
	public $clave;
	
	public function rules()
	{
		return [
			[['nombre', 'apellido', 'edad', 'email', 'clave'], 'required'],
			[['nombre', 'apellido', 'edad', 'email', 'clave'], 'string', 'max' => 150],
		];
	}
}