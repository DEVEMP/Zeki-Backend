<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\Estados;
use common\models\web\Mensajes;
use common\models\web\Acciones;
use common\models\web\EstadosMensajes;

class EstadosController extends CController
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
		
		return $this->render('/web/estados/index', [
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
		
		$filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : '';
		$filtro_tipo_estado = isset($_POST['filtro_tipo_estado']) ? $_POST['filtro_tipo_estado'] : '';
		$filtro_palabras_claves_esp = isset($_POST['filtro_palabras_claves_esp']) ? $_POST['filtro_palabras_claves_esp'] : '';
		$filtro_palabras_claves_eng = isset($_POST['filtro_palabras_claves_eng']) ? $_POST['filtro_palabras_claves_eng'] : '';
		$filtro_palabras_claves_tur = isset($_POST['filtro_palabras_claves_tur']) ? $_POST['filtro_palabras_claves_tur'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = Estados::find();
		

		if($filtro_estado != '')
		{
			$modelo = $modelo->andWhere(['estado' => $filtro_estado]);
		}
		if($filtro_tipo_estado != '')
		{
			$modelo = $modelo->andWhere(['tipo_estado' => $filtro_tipo_estado]);
		}
		if($filtro_palabras_claves_esp != '')
		{
			$modelo = $modelo->andWhere(['like', 'palabras_claves_esp', $filtro_palabras_claves_esp]);
		}
		if($filtro_palabras_claves_eng != '')
		{
			$modelo = $modelo->andWhere(['like', 'palabras_claves_eng', $filtro_palabras_claves_eng]);
		}
		if($filtro_palabras_claves_tur != '')
		{
			$modelo = $modelo->andWhere(['like', 'palabras_claves_tur', $filtro_palabras_claves_tur]);
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
			$mensajes_html = '';
			$listado_estados_mensajes = EstadosMensajes::find()
			->andWhere(['estado' => $modelo->id])
			->all();
			
			foreach($listado_estados_mensajes as $estado_mensaje)
			{
			    if($mensajes_html == ''){
			        $mensajes_html .= '<div><small>' . $estado_mensaje->mensaje0->mensaje_esp . '</small></div>';
			    }
			    else{
			        $mensajes_html .= '<hr/>';
			        $mensajes_html .= '<div><small>' . $estado_mensaje->mensaje0->mensaje_esp . '</small></div>';
			    }
			}
		    
		    
		    $listado_modelos_array[] = [
				'id' => $modelo->id,
				'estado' => $modelo->estado,
				'tipo_estado' => $modelo->tipo_estado,
				'palabras_claves_esp' => $modelo->palabras_claves_esp,
				'palabras_claves_eng' => $modelo->palabras_claves_eng,
				'palabras_claves_tur' => $modelo->palabras_claves_tur,
				'mensajes' => $mensajes_html,
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

		
		// PARA EL LISTADO DE MENSAJES
		$listado_mensajes = Mensajes::find()
		->orderBy('mensaje_esp ASC')
		->all();
		
		
		
		$modelo = new Estados();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$mensaje_exito = 'El Estado ha sido ingresado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/estados/editar/' . $modelo->id
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/estados/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
			
			'listado_mensajes' => $listado_mensajes,
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
		
		
		// PARA EL LISTADO DE MENSAJES
		$listado_mensajes = Mensajes::find()
		->orderBy('mensaje_esp ASC')
		->all();
		
		
		$modelo = Estados::find()->where(['id' => $id])->one();
		
		$mensaje_error = '';
		$mensaje_exito = '';
		if(Yii::$app->request->isPost)
		{
			$modelo->load(Yii::$app->request->post());
			
			if($modelo->validate())
			{
				$modelo->save();
				
				
				$mensaje_exito = 'El Estado ha sido editado exitosamente.';
				Yii::$app->session['mensaje_flash'] = $mensaje_exito;
				
				Yii::$app->getResponse()->redirect([
					'web/estados/editar/' . $modelo->id
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/estados/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
			
			'listado_mensajes' => $listado_mensajes,
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
			$modelo = Estados::find()->where(['id' => $_POST['id']])->one();
			if($modelo != null)
			{
				// PARA BUSCAR LAS ACCIONES DE LOS ESTADOS
				$acciones_bd = Acciones::find()
				->andWhere(['origen' => $modelo->id])
				->orWhere(['destino' => $modelo->id])
				->all();
				
				foreach($acciones_bd as $accion)
				{
					$accion->delete();
				}				
				
				$modelo->delete();
				
				$json_respuesta = array(
					'codigo' => '1',
					'mensaje' => 'El estado se ha eliminado exitosamente...',
				);
			}
			else
			{
				$json_respuesta = array(
					'codigo' => '0',
					'mensaje' => 'Ha ocurrido un error al intentar eliminar el estado...',
				);
			}
		}
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
}
