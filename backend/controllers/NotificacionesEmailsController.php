<?php

namespace backend\controllers;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\NotificacionesEmails;
use common\models\Notificaciones;

class NotificacionesEmailsController extends CController
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
		
		return $this->render('/notificacionesemails/index', [
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
		
		$filtro_para = isset($_POST['filtro_para']) ? $_POST['filtro_para'] : '';
		$filtro_titulo = isset($_POST['filtro_titulo']) ? $_POST['filtro_titulo'] : '';
		$filtro_notificacion = isset($_POST['filtro_notificacion']) ? $_POST['filtro_notificacion'] : '';
		$filtro_enviado = isset($_POST['filtro_enviado']) ? $_POST['filtro_enviado'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = NotificacionesEmails::find();
		
		
		if($filtro_para != '')
		{
			$modelo = $modelo->andWhere(['id' => $filtro_para]);
		}
		if($filtro_titulo != '')
		{
			$modelo = $modelo->andWhere(['orden' => $filtro_titulo]);
		}
		if($filtro_notificacion != '')
		{
			$modelo = $modelo->andWhere(['like', 'notificacionesemail', $filtro_notificacion]);
		}
		if($filtro_enviado != '')
		{
			$modelo = $modelo->andWhere(['like', 'valor', $filtro_enviado]);
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
			$listado_modelos_array[] = [
				'id' => $modelo->id,
				'para' => $modelo->para,
				'titulo' => $modelo->titulo,
				'variables' => $modelo->variables,
				'enviado' => $modelo->enviado,
				'fecha_creacion' => $modelo->fecha_creacion,
				'notificacion' => $modelo->notificacion0->identificador,
			];
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
		
		
		// PARA EL LISTADO DE NOTIFICACIONES
		$notificaciones = Notificaciones::find()->all();
		$listado_notificaciones = ['' => 'Seleccione una opcion'];
		$listado_notificaciones = ArrayHelper::merge($listado_notificaciones, ArrayHelper::map($notificaciones, 'id', 'identificador'));
		
		
		$modelo = new NotificacionesEmails();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			if($modelo->validate())
			{
				$modelo->fecha_creacion = (new \DateTime())->format('Y-m-d H:i:s');
				$modelo->save();
				
				$mensaje_exito = 'El Notificaciones Email ha sido ingresado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'notificaciones-emails/index'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/notificacionesemails/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
			
			'listado_notificaciones' => $listado_notificaciones,
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
		
		
		// PARA EL LISTADO DE NOTIFICACIONES
		$notificaciones = Notificaciones::find()->all();
		$listado_notificaciones = ['' => 'Seleccione una opcion'];
		$listado_notificaciones = \yii\helpers\ArrayHelper::merge($listado_notificaciones, \yii\helpers\ArrayHelper::map($notificaciones, 'id', 'identificador'));
		
		
		$modelo = NotificacionesEmails::find()->where(['id' => $id])->one();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			if($modelo->validate())
			{
				Yii::trace($modelo->save());
				Yii::trace($modelo->getErrors());
				
				$mensaje_exito = 'El NotificacionesEmail ha sido editado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'notificaciones-emails/index'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/notificacionesemails/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
			
			'listado_notificaciones' => $listado_notificaciones,
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
			$modelo = NotificacionesEmails::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'mensaje' => 'El notificacionesemail se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'mensaje' => 'Ha ocurrido un error al intentar eliminar el notificacionesemail...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	public function actionGeneradorEmails()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		
		// PARA GENERAR EL CORREO DE RECUPERACION DE CONTRASEÑA
		$valores = [];
		array_push($valores, ['nombre' => 'variable1', 'valor' => 'valor1']);
		array_push($valores, ['nombre' => 'variable2', 'valor' => 'valor2']);
		array_push($valores, ['nombre' => 'variable3', 'valor' => 'valor3']);
		$utilidades->generarEmails('univ3r5al89@gmail.com', "Email de Pruebas", "email_prueba", $valores);
		
		
		$mensaje_exito = 'El email de pruebas ha sido generado exitosamente.';
		Yii::$app->session['mensaje_flash'] = $mensaje_exito;
		
		Yii::$app->getResponse()->redirect([
			'notificaciones-emails/index'
		])->send();
		return;
	}
	
	public function actionEnviarEmails()
	{
		$utilidades = new Utilidades();
		if(! $utilidades->haIniciadoSesionBackend())
		{
			Yii::$app->getResponse()->redirect([
				'main/ingresar'
			])->send();
			return;
		}
		
		
		// PARA ENVIAR TODOS LOS CORREOS
		$utilidades->enviarEmails();
		
		
		$mensaje_exito = 'Los emails pendientes se han enviado exitosamente.';
		Yii::$app->session['mensaje_flash'] = $mensaje_exito;
		
		Yii::$app->getResponse()->redirect([
			'notificaciones-emails/index'
		])->send();
		return;
	}
}
