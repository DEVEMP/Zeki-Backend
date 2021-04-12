<?php

namespace common\forms;

use yii\base\Model;

class MiCuentaForm extends Model
{
	public $usuario;
	public $nombre;
	public $apellido;
	public $email;
	
	public function rules()
	{
		return [
			[['usuario', 'nombre', 'apellido', 'email'], 'required'],
			[['usuario', 'nombre', 'apellido'], 'string', 'max' => 60],
			[['email'], 'string', 'max' => 120],
		];
	}
}