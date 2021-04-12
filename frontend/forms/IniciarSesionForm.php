<?php

namespace frontend\forms;

use yii\base\Model;

class IniciarSesionForm extends Model
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
	
	public function attributeLabels()
	{
		return [
			'usuario' => 'Usuario',
			'clave' => 'Contraseña',
		];
	}
}