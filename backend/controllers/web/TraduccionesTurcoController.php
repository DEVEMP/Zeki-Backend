<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\TraduccionesTurco;

class TraduccionesTurcoController extends CController
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
		
		$accion_flash = Yii::$app->session['accion_flash'];
		Yii::$app->session['accion_flash'] = '';
		
		return $this->render('/web/traduccionesturco/index', [
			'accion_flash' => $accion_flash,
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
		$filtro_frase = isset($_POST['filtro_frase']) ? $_POST['filtro_frase'] : '';
		$filtro_traduccion = isset($_POST['filtro_traduccion']) ? $_POST['filtro_traduccion'] : '';
		$filtro_palabras = isset($_POST['filtro_palabras']) ? $_POST['filtro_palabras'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = TraduccionesTurco::find();
		

		if($filtro_id != '')
		{
			$modelo = $modelo->andWhere(['id' => $filtro_id]);
		}
		if($filtro_frase != '')
		{
			$modelo = $modelo->andWhere(['like', 'frase', $filtro_frase]);
		}
		if($filtro_traduccion != '')
		{
			$modelo = $modelo->andWhere(['like', 'traduccion', $filtro_traduccion]);
		}
		if($filtro_palabras != '')
		{
			$modelo = $modelo->andWhere(['palabras' => $filtro_palabras]);
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
				'frase' => $modelo->frase,
				'traduccion' => $modelo->traduccion,
				'palabras' => $modelo->palabras,
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
		
		
		$modelo = new TraduccionesTurco();
		
		$accion_error = '';
		$accion_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$accion_exito = 'La TraduccionTurco ha sido ingresado exitosamente.';
				Yii::$app->session['accion_flash'] = $accion_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/traduccionesturco'
				])->send();
				return;
			}
			else
				$accion_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/traduccionesturco/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'accion_error' => $accion_error,
			'accion_exito' => $accion_exito,
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
		
		
		$modelo = TraduccionesTurco::find()->where(['id' => $id])->one();
		
		$accion_error = '';
		$accion_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$accion_exito = 'La TraduccionTurco ha sido editado exitosamente.';
				Yii::$app->session['accion_flash'] = $accion_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/traduccionesturco'
				])->send();
				return;
			}
			else
				$accion_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/traduccionesturco/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'accion_error' => $accion_error,
			'accion_exito' => $accion_exito,
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
			$modelo = TraduccionesTurco::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'accion' => 'La traduccionturco se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'accion' => 'Ha ocurrido un error al intentar eliminar la traduccionturco...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
}
