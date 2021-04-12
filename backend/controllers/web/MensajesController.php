<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\Mensajes;

class MensajesController extends CController
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
		
		return $this->render('/web/mensajes/index', [
			'mensaje_flash' => $mensaje_flash,
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
		$filtro_mensaje_esp = isset($_POST['filtro_mensaje_esp']) ? $_POST['filtro_mensaje_esp'] : '';
		$filtro_mensaje_eng = isset($_POST['filtro_mensaje_eng']) ? $_POST['filtro_mensaje_eng'] : '';
		$filtro_mensaje_tur = isset($_POST['filtro_mensaje_tur']) ? $_POST['filtro_mensaje_tur'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = Mensajes::find();
		

		if($filtro_id != '')
		{
			$modelo = $modelo->andWhere(['id' => $filtro_id]);
		}
		if($filtro_mensaje_esp != '')
		{
			$modelo = $modelo->andWhere(['like', 'mensaje_esp', $filtro_mensaje_esp]);
		}
		if($filtro_mensaje_eng != '')
		{
			$modelo = $modelo->andWhere(['like', 'mensaje_eng', $filtro_mensaje_eng]);
		}
		if($filtro_mensaje_tur != '')
		{
			$modelo = $modelo->andWhere(['like', 'mensaje_tur', $filtro_mensaje_tur]);
		}
		
		
		// PARA INDICAR EL ORDEN DE LA CONSULTA
		$modelo = $modelo->orderBy($orden_campo);
		
		
		// PARA LA PAGINACION
		$cantidad_modelos = $modelo->count();
		$cantidad_por_pagina = Parametros::find()->where(['orden' => '11'])->one()->valor;
		
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
				'mensaje_esp' => $modelo->mensaje_esp,
				'mensaje_eng' => $modelo->mensaje_eng,
				'mensaje_tur' => $modelo->mensaje_tur,
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
		
		
		$modelo = new Mensajes();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$mensaje_exito = 'El Mensaje ha sido ingresado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/mensajes'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/mensajes/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
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
		
		
		
		$modelo = Mensajes::find()->where(['id' => $id])->one();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$mensaje_exito = 'El Mensaje ha sido editado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/mensajes'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/mensajes/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
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
			$modelo = Mensajes::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'mensaje' => 'El mensaje se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'mensaje' => 'Ha ocurrido un error al intentar eliminar el mensaje...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
}
