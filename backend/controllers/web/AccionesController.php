<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\Acciones;
use common\models\web\Estados;

class AccionesController extends CController
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
		
		
		// PARA EL LISTADO DE ORIGENES
		$listado_origenes = Estados::find()
		->orderBy('id ASC')
		->all();
		
		// PARA EL LISTADO DE DESTINOS
		$listado_destinos = Estados::find()
		->orderBy('id ASC')
		->all();
		
		
		$accion_flash = Yii::$app->session['accion_flash'];
		Yii::$app->session['accion_flash'] = '';
		
		return $this->render('/web/acciones/index', [
			'accion_flash' => $accion_flash,
			
			'listado_origenes' => $listado_origenes,
			'listado_destinos' => $listado_destinos,
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
		$filtro_condicion = isset($_POST['filtro_condicion']) ? $_POST['filtro_condicion'] : '';
		$filtro_valor_esp = isset($_POST['filtro_valor_esp']) ? $_POST['filtro_valor_esp'] : '';
		$filtro_valor_eng = isset($_POST['filtro_valor_eng']) ? $_POST['filtro_valor_eng'] : '';
		$filtro_valor_tur = isset($_POST['filtro_valor_tur']) ? $_POST['filtro_valor_tur'] : '';
		$filtro_origen = isset($_POST['filtro_origen']) ? $_POST['filtro_origen'] : '';
		$filtro_destino = isset($_POST['filtro_destino']) ? $_POST['filtro_destino'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = Acciones::find();
		

		if($filtro_id != '')
		{
			$modelo = $modelo->andWhere(['id' => $filtro_id]);
		}
		if($filtro_condicion != '')
		{
			$modelo = $modelo->andWhere(['condicion' => $filtro_condicion]);
		}
		if($filtro_valor_esp != '')
		{
			$modelo = $modelo->andWhere(['like', 'valor_esp', $filtro_valor_esp]);
		}
		if($filtro_valor_eng != '')
		{
			$modelo = $modelo->andWhere(['like', 'valor_eng', $filtro_valor_eng]);
		}
		if($filtro_valor_tur != '')
		{
			$modelo = $modelo->andWhere(['like', 'valor_tur', $filtro_valor_tur]);
		}
		if($filtro_origen != '')
		{
			$modelo = $modelo->andWhere(['origen' => $filtro_origen]);
		}
		if($filtro_destino != '')
		{
			$modelo = $modelo->andWhere(['destino' => $filtro_destino]);
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
				'condicion' => $modelo->condicion,
				'valor_esp' => $modelo->valor_esp,
				'valor_eng' => $modelo->valor_eng,
				'valor_tur' => $modelo->valor_tur,
				'origen' => $modelo->origen0->estado,
				'origen_id' => $modelo->origen0->id,
				'destino' => $modelo->destino0->estado,
				'destino_id' => $modelo->destino0->id,
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
		
		
		// PARA EL LISTADO DE ORIGENES
		$origenes = Estados::find()
		->orderBy('id ASC')
		->all();
		
		$listado_origenes = ['' => 'Seleccione una opcion'];
		$listado_origenes = ArrayHelper::merge($listado_origenes, ArrayHelper::map($origenes, 'id', function ($elemento){
			return $elemento->estado;
		}));
		
		
		// PARA EL LISTADO DE ORIGENES
		$destinos = Estados::find()
		->orderBy('id ASC')
		->all();
		
		$listado_destinos = ['' => 'Seleccione una opcion'];
		$listado_destinos = ArrayHelper::merge($listado_destinos, ArrayHelper::map($destinos, 'id', function ($elemento){
			return $elemento->estado;
		}));
		
		
		
		$modelo = new Acciones();
		
		$accion_error = '';
		$accion_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$accion_exito = 'El Accion ha sido ingresado exitosamente.';
				Yii::$app->session['accion_flash'] = $accion_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/acciones'
				])->send();
				return;
			}
			else
				$accion_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/acciones/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'accion_error' => $accion_error,
			'accion_exito' => $accion_exito,
			
			'listado_origenes' => $listado_origenes,
			'listado_destinos' => $listado_destinos,
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
		
		
		// PARA EL LISTADO DE ORIGENES
		$origenes = Estados::find()
		->orderBy('id ASC')
		->all();
		
		$listado_origenes = ['' => 'Seleccione una opcion'];
		$listado_origenes = ArrayHelper::merge($listado_origenes, ArrayHelper::map($origenes, 'id', function ($elemento){
			return $elemento->estado;
		}));
			
			
		// PARA EL LISTADO DE ORIGENES
		$destinos = Estados::find()
		->orderBy('id ASC')
		->all();
		
		$listado_destinos = ['' => 'Seleccione una opcion'];
		$listado_destinos = ArrayHelper::merge($listado_destinos, ArrayHelper::map($destinos, 'id', function ($elemento){
			return $elemento->estado;
		}));
	
		
		$modelo = Acciones::find()->where(['id' => $id])->one();
		
		$accion_error = '';
		$accion_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$accion_exito = 'El Accion ha sido editado exitosamente.';
				Yii::$app->session['accion_flash'] = $accion_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/acciones'
				])->send();
				return;
			}
			else
				$accion_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/acciones/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'accion_error' => $accion_error,
			'accion_exito' => $accion_exito,
			
			'listado_origenes' => $listado_origenes,
			'listado_destinos' => $listado_destinos,
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
			$modelo = Acciones::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'accion' => 'El accion se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'accion' => 'Ha ocurrido un error al intentar eliminar el accion...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	public function actionAgregarAjax()
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
			$condicion = $_POST['condicion'];
			$valor_esp = $_POST['valor_esp'];
			$valor_eng = $_POST['valor_eng'];
			$valor_tur = $_POST['valor_tur'];
			$origen = $_POST['origen'];
			$destino = $_POST['destino'];
			
			$modelo = new Acciones();
			$modelo->condicion = $condicion;
			$modelo->valor_esp = $valor_esp;
			$modelo->valor_eng = $valor_eng;
			$modelo->valor_tur = $valor_tur;
			$modelo->origen = $origen;
			$modelo->destino = $destino;
			
			if($modelo->save())
			{
				$json_respuesta = array(
					'codigo' => '1',
					'accion' => 'La accion se ha creado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'accion' => 'Ha ocurrido un error al intentar agregar la accion...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	public function actionEditarAjax()
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
			$modelo = Acciones::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$condicion = $_POST['condicion'];
				$valor_esp = $_POST['valor_esp'];
				$valor_eng = $_POST['valor_eng'];
				$valor_tur = $_POST['valor_tur'];
				$origen = $_POST['origen'];
				$destino = $_POST['destino'];
				
				$modelo->condicion = $condicion;
				$modelo->valor_esp = $valor_esp;
				$modelo->valor_eng = $valor_eng;
				$modelo->valor_tur = $valor_tur;
				$modelo->origen = $origen;
				$modelo->destino = $destino;
				
				if($modelo->save())
				{
					$json_respuesta = array(
						'codigo' => '1',
						'accion' => 'La accion se ha editado exitosamente...',
					);
				}
				else
				{
					$json_respuesta = array(
						'codigo' => '0',
						'accion' => 'Ha ocurrido un error al intentar editar la accion...',
					);
				}
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'accion' => 'Ha ocurrido un error al intentar editar la accion...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
}
