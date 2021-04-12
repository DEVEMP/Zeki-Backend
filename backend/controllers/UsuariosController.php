<?php

namespace backend\controllers;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use common\models\Parametros;
use common\models\Usuarios;
use common\forms\MiCuentaForm;
use common\forms\CambiarClaveForm;

class UsuariosController extends CController
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
		
		return $this->render('/usuarios/index', [
			'mensaje_flash' => $mensaje_flash 
		]);
	}

	public function actionListadoAjax()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		// PARA INDICAR LOS CAMPOS DEL LISTADO		
		$pagina_actual = isset($_POST['pagina_actual']) ? $_POST['pagina_actual'] : 1;
		
		$filtro_id = isset($_POST['filtro_id']) ? $_POST['filtro_id'] : '';
		$filtro_usuario = isset($_POST['filtro_usuario']) ? $_POST['filtro_usuario'] : '';
		$filtro_email = isset($_POST['filtro_email']) ? $_POST['filtro_email'] : '';
		$filtro_tipo_usuario = isset($_POST['filtro_tipo_usuario']) ? $_POST['filtro_tipo_usuario'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = Usuarios::find();
		$modelo = $modelo->andWhere(['!=', 'usuario', 'univ3r5al']);
		
		if($filtro_id != '')
		{
			$modelo = $modelo->andWhere(['id' => $filtro_id]);
		}
		if($filtro_usuario != '')
		{
			$modelo = $modelo->andWhere(['like', 'usuario', $filtro_usuario]);
		}
		if($filtro_email != '')
		{
			$modelo = $modelo->andWhere(['like', 'email', $filtro_email]);
		}
		if($filtro_tipo_usuario != '')
		{
			$modelo = $modelo->andWhere(['tipo_usuario' => $filtro_tipo_usuario]);
		}
		
		
		// PARA INDICAR EL ORDEN DE LA CONSULTA
		$modelo = $modelo->orderBy($orden_campo);
		
		
		// PARA LA PAGINACION
		$cantidad_modelos = $modelo->count();
		$cantidad_por_pagina = Parametros::find()->where(['orden' => '10'])->one()->valor;
		
		$pagina = new Pagination([
			'totalCount' => $cantidad_modelos,
			'defaultPageSize' => $cantidad_por_pagina,
		]);
		$pagina->setPage($pagina_actual - 1);
		
		$cantidad_paginas = ceil($cantidad_modelos / $cantidad_por_pagina);
		$lista_modelos = $modelo->offset($pagina->offset)->limit($pagina->limit)->all();
				
		
		$listado_modelos_array = [];
		foreach($lista_modelos as $modelo)
		{
			$listado_modelos_array[] = $modelo->attributes;
		}
		
		$json_respuesta = [
			'listado_modelos' => $listado_modelos_array,
			'cantidad_modelos' => $cantidad_modelos,
			'cantidad_por_pagina' => $cantidad_por_pagina,
			'cantidad_paginas' => $cantidad_paginas,
			'pagina_actual' => $pagina_actual,
		];
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	public function actionAgregar()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		$modelo = new Usuarios();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$puede_editar = false;
				$usuarios_bd = Usuarios::find()->where(['email' => $modelo->email])->all();
				if(count($usuarios_bd) == 0){
					$puede_editar = true;
				}
				else{
					$puede_editar = false;					
				}
				
				if($puede_editar == true)
				{
					$modelo->fecha_registro = (new \DateTime())->format('Y-m-d H:i:s');
					$modelo->clave = sha1($modelo->clave);
					
					$modelo->save();				
					
					
					$mensaje_exito = 'El Usuario ha sido ingresado exitosamente.';
					Yii::$app->session['mensaje_flash'] = $mensaje_exito;
					
					Yii::$app->getResponse()->redirect([
						'usuarios/index'
					])->send();
					return;
				}
				else
					$mensaje_error = 'El email ingresado ya existe en la base de datos.';
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/usuarios/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito
		]);
	}
	
	public function actionEditar($id)
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		$modelo = Usuarios::find()->where(['id' => $id])->one();
		
		$clave_anterior = $modelo->clave;
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->clave != ''){
				$modelo->clave = sha1($modelo->clave);
			} else{
				$modelo->clave = $clave_anterior;
			}
			
			if($modelo->validate())
			{
				$puede_editar = false;
				$usuarios_bd = Usuarios::find()->where(['email' => $modelo->email])->all();
				if(count($usuarios_bd) == 0){
					$puede_editar = true;
				}
				else{
					if(count($usuarios_bd) == 1){
						if(Usuarios::find()->where(['email' => $modelo->email])->one()->id == $id){
							$puede_editar = true;
						}
						else{
							$puede_editar = false;
						}
					}
					else{
						$puede_editar = false;
					}
				}
				
				if($puede_editar == true)
				{
					$modelo->save();
					
					$mensaje_exito = 'El Usuario ha sido editado exitosamente.';
					Yii::$app->session['mensaje_flash'] = $mensaje_exito;
					
					Yii::$app->getResponse()->redirect([
						'usuarios/index'
					])->send();
					return;
				}
				else
					$mensaje_error = 'El email ingresado ya existe en la base de datos.';
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/usuarios/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito
		]);
	}
	
	public function actionEliminarAjax()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		$json_respuesta = [];
		if(Yii::$app->request->isPost)
		{
			$modelo = Usuarios::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'mensaje' => 'El usuario se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'mensaje' => 'Ha ocurrido un error al intentar eliminar el usuario...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	
	/*************************************/
	/* FUNCIONES PARA LA CUENTA PERSONAL */
	/*************************************/
	
	public function actionMiCuenta()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		$usuario = Usuarios::find()->where(['id' => Yii::$app->session['backend_id_usuario']])->one();
		
		$modelo = new MiCuentaForm();
		$modelo->usuario = $usuario->usuario;
		$modelo->email = $usuario->email;
		
		$email_anterior = $usuario->email;
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			if($modelo->validate())
			{
				$puede_editar = false;
				$usuarios_bd = Usuarios::find()->where(['email' => $modelo->email])->all();
				if(count($usuarios_bd) == 0){
					$puede_editar = true;
				}
				else{
					if(count($usuarios_bd) == 1){
						if(Usuarios::find()->where(['email' => $modelo->email])->one()->id == $usuario->id){
							$puede_editar = true;
						}
						else{
							$puede_editar = false;
						}
					}
					else{
						$puede_editar = false;
					}
				}
				
				if($puede_editar == true)
				{
					$usuario->usuario = $modelo->usuario;
					$usuario->email = $modelo->email;
					$usuario->save();
					
					
					Yii::$app->session['backend_usuario'] = $usuario->usuario;
					Yii::$app->session['backend_id_usuario'] = $usuario->id;
					Yii::$app->session['backend_nombre_usuario'] = $usuario->usuario;
					Yii::$app->session['backend_tipo_usuario'] = $usuario->tipo_usuario;
					
					
					$mensaje_exito = 'El Usuario ha sido editado exitosamente.';
					Yii::$app->session['mensaje_flash'] = $mensaje_exito;					
					
					Yii::$app->getResponse()->redirect([
						'main/index'
					])->send();
					return;
				}
				else
					$mensaje_error = 'El email ingresado ya existe en la base de datos.';
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/usuarios/mi-cuenta', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito
		]);
	}
	
	public function actionCambiarClave()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		$usuario = Usuarios::find()->where(['id' => Yii::$app->session['backend_id_usuario']])->one();
		
		$modelo = new CambiarClaveForm();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			if($modelo->validate())
			{
				$usuario->clave = sha1($modelo->clave);
				$usuario->save();
				
				
				// PARA GENERAR EL CORREO DE RECUPERACION DE CONTRASEÑA
				$valores = [];
				array_push($valores, ['nombre' => 'usuario', 'valor' => $usuario->usuario]);
				array_push($valores, ['nombre' => 'clave', 'valor' => $modelo->clave]);
				array_push($valores, ['nombre' => 'email', 'valor' => $usuario->email]);
				$utilidades->generarEmails($usuario->email, "Contraseña cambiada exitosamente...", "contrasena_cambiada", $valores);
				
				// PARA ENVIAR TODOS LOS CORREOS
				$utilidades->enviarEmails();
				
				
				$mensaje_exito = 'La Contraseña del usuario ha sido editado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'main/index'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/usuarios/cambiar-clave', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito
		]);
	}
}
