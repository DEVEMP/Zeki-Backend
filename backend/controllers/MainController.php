<?php

namespace backend\controllers;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;
use common\forms\IngresarForm;
use common\models\Usuarios;
use common\forms\RecuperarClaveForm;

class MainController extends CController
{

	public function actionIndex()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar' 
			])->send();
			return;
		}
		
		$mensaje_flash = Yii::$app->session['mensaje_flash'];
		Yii::$app->session['mensaje_flash'] = '';
		
		return $this->render('/index', [
			'mensaje_flash' => $mensaje_flash 
		]);
	}

	public function actionIngresar()
	{
		$utilidades = new Utilidades();
		if($utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/index' 
			])->send();
			return;
		}
		
		$model = new IngresarForm();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$model->load(Yii::$app->request->post());			
			if($model->validate())
			{
				$usuario = Usuarios::find()->where([
					'usuario' => $model->usuario,
					'clave' => sha1($model->clave) 
				])->one();
				
				if($usuario != null)
				{
					Yii::$app->session['backend_usuario'] = $usuario->usuario;
					Yii::$app->session['backend_id_usuario'] = $usuario->id;
					Yii::$app->session['backend_nombre_usuario'] = $usuario->usuario;
					Yii::$app->session['backend_tipo_usuario'] = $usuario->tipo_usuario;
					
					Yii::$app->session['mensaje_flash'] = '';
					
					Yii::$app->getResponse()->redirect([
						'main/index' 
					])->send();
					return;
				}
				else
					$mensaje_error = 'El usuario y/o contraseña ingresados no existe en la BD.';
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/ingresar', [
			'model' => $model,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito 
		]);
	}
	
	public function actionRecuperarClave()
	{
		$utilidades = new Utilidades();
		if($utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/index'
			])->send();
			return;
		}
		
		$model = new RecuperarClaveForm();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$model->load(Yii::$app->request->post());
			if($model->validate())
			{
				$usuario = Usuarios::find()->where([
					'email' => $model->email,
				])->one();
				
				if($usuario != null)
				{
					$nueva_clave = substr(sha1($usuario->clave), 0, 6);
					
					$usuario->clave = sha1($nueva_clave);
					$usuario->save();
					
					
					// PARA GENERAR EL CORREO DE RECUPERACION DE CONTRASEÑA
					$valores = [];
					array_push($valores, ['nombre' => 'usuario', 'valor' => $usuario->usuario]);
					array_push($valores, ['nombre' => 'clave', 'valor' => $nueva_clave]);
					array_push($valores, ['nombre' => 'email', 'valor' => $usuario->email]);
					$utilidades->generarEmails($usuario->email, "Contraseña cambiada exitosamente...", "contrasena_cambiada", $valores);
										
					// PARA ENVIAR TODOS LOS CORREOS
					$utilidades->enviarEmails();
					
					
					$mensaje_exito = 'Su nueva contraseña ha sido enviada a su correo electrónico';
					
					$model = new RecuperarClaveForm();
				}
				else
					$mensaje_error = 'El usuario y/o contraseña ingresados no existe en la BD.';
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/recuperar', [
			'model' => $model,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito
		]);
	}
	
	public function actionSalir()
	{
		unset(Yii::$app->session['backend_usuario']);
		unset(Yii::$app->session['backend_id_usuario']);
		unset(Yii::$app->session['backend_nombre_usuario']);
		unset(Yii::$app->session['backend_tipo_usuario']);
		
		unset(Yii::$app->session['mensaje_flash']);
		
		Yii::$app->getResponse()->redirect([
			'main/index'
		])->send();
		return;
	}
}
