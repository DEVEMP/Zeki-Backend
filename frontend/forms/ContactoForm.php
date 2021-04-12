<?php

namespace frontend\forms;

use yii\base\Model;

class ContactoForm extends Model
{
	public $nombre;
	public $email;
	public $telefono;
	public $mensaje;
	
	public function rules()
	{
		return [
			[['nombre', 'email', 'telefono', 'mensaje'], 'required'],
			[['nombre', 'email', 'telefono'], 'string', 'max' => 60],
			[['mensaje'], 'string'],
		];
	}
}