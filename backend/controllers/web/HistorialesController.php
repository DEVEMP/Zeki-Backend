<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\Historiales;
use common\models\web\Medicos;

class HistorialesController extends CController
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
		
		
		// PARA EL LISTADO DE MEDICOS
		$listado_medicos = Medicos::find()
		->orderBy('id ASC')
		->all();
		
		
		$historial_flash = Yii::$app->session['historial_flash'];
		Yii::$app->session['historial_flash'] = '';
		
		return $this->render('/web/historiales/index', [
			'historial_flash' => $historial_flash,
			
			'listado_medicos' => $listado_medicos,
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
		
		$filtro_identificador = isset($_POST['filtro_identificador']) ? $_POST['filtro_identificador'] : '';
		$filtro_medico = isset($_POST['filtro_medico']) ? $_POST['filtro_medico'] : '';
		$filtro_fecha_mensaje_desde = isset($_POST['filtro_fecha_mensaje_desde']) ? $_POST['filtro_fecha_mensaje_desde'] : '';
		$filtro_fecha_mensaje_hasta = isset($_POST['filtro_fecha_mensaje_hasta']) ? $_POST['filtro_fecha_mensaje_hasta'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = Historiales::find();
		

		if($filtro_identificador != '')
		{
			$modelo = $modelo->andWhere(['identificador' => $filtro_identificador]);
		}
		if($filtro_medico != '')
		{
			$modelo = $modelo->andWhere(['medico' => $filtro_medico]);
		}
		if($filtro_fecha_mensaje_desde != '')
		{
		    $fecha_minima = date_create_from_format('d/m/Y H:i:s', $filtro_fecha_mensaje_desde . ' 00:00:00');
		    $modelo = $modelo->andWhere(['>=', 'fecha_mensaje', $fecha_minima->format('Y-m-d H:i:s')]);
		}
		if($filtro_fecha_mensaje_hasta != '')
		{
		    $fecha_maxima = date_create_from_format('d/m/Y H:i:s', $filtro_fecha_mensaje_hasta . ' 23:59:59');
		    $modelo = $modelo->andWhere(['<=', 'fecha_mensaje', $fecha_maxima->format('Y-m-d H:i:s')]);
		}
		
		$modelo = $modelo->groupBy(['identificador']);
		
		
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
		    $fecha_mensaje = (new \DateTime())->createFromFormat('Y-m-d H:i:s', $modelo->fecha_mensaje);
		    
		    $listado_modelos_array[] = [
				'id' => $modelo->id,
				'identificador' => $modelo->identificador,
				'medico' => $modelo->medico0->nombre . ' ' . $modelo->medico0->apellido,
			    'fecha_mensaje' => $fecha_mensaje->format("d/m/Y H:i:s"),
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
		
		
		// PARA EL LISTADO DE MEDICOS
		$medicos = Medicos::find()
		->orderBy('id ASC')
		->all();
		
		$listado_medicos = ['' => 'Seleccione una opcion'];
		$listado_medicos = ArrayHelper::merge($listado_medicos, ArrayHelper::map($medicos, 'id', function ($elemento){
			return $elemento->nombre . ' ' . $elemento->apellido;
		}));

		
		
		$modelo = new Historiales();
		
		$historial_error = '';
		$historial_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
			    $modelo->save();
				
				
				$historial_exito = 'El Historial ha sido ingresado exitosamente.';
				Yii::$app->session['historial_flash'] = $historial_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/historiales/visualizar/' . $modelo->id
				])->send();
				return;
			}
			else
				$historial_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/historiales/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'historial_error' => $historial_error,
			'historial_exito' => $historial_exito,
			
			'listado_medicos' => $listado_medicos,
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
		
		
		// PARA EL LISTADO DE MEDICOS
		$medicos = Medicos::find()
		->orderBy('id ASC')
		->all();
		
		$listado_medicos = ['' => 'Seleccione una opcion'];
		$listado_medicos = ArrayHelper::merge($listado_medicos, ArrayHelper::map($medicos, 'id', function ($elemento){
		    return $elemento->nombre . ' ' . $elemento->apellido;
		}));
	
		
		$modelo = Historiales::find()->where(['id' => $id])->one();
		
		$historial_error = '';
		$historial_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$historial_exito = 'El Historial ha sido editado exitosamente.';
				Yii::$app->session['historial_flash'] = $historial_exito;
				
				Yii::$app->getResponse()->redirect([
				    'web/historiales/visualizar/' . $modelo->id
				])->send();
				return;
			}
			else
				$historial_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/historiales/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'historial_error' => $historial_error,
			'historial_exito' => $historial_exito,
			
		    'listado_medicos' => $listado_medicos,
		]);
	}
	
	public function actionVisualizar($id)
	{
	    $utilidades = new Utilidades();
	    if(! $utilidades->haIniciadoSesionBackend())
	    {
	        Yii::$app->getResponse()->redirect([
	            'main/ingresar'
	        ])->send();
	        return;
	    }
	        
        $modelo = Historiales::find()->where(['id' => $id])->one();
        
        
        $listado_historiales = Historiales::find()
        ->andWhere(['identificador' => $modelo->identificador])
        ->orderBy('fecha_mensaje ASC')
        ->all();
        
        
        return $this->render('/web/historiales/visualizar', [
            'modelo' => $modelo,
            'listado_historiales' => $listado_historiales,
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
			$modelo = Historiales::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'historial' => 'El historial se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'historial' => 'Ha ocurrido un error al intentar eliminar el historial...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	
}
