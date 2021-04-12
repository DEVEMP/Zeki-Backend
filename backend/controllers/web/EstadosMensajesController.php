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
use common\models\web\Estados;
use common\models\web\EstadosMensajes;

class EstadosMensajesController extends CController
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
		
		
		// PARA EL LISTADO DE ESTADOS
		$listado_estados = Estados::find()
		->orderBy('id ASC')
		->all();
		
		
		// PARA EL LISTADO DE MENSAJES
		$listado_mensajes = Mensajes::find()
		->orderBy('id ASC')
		->all();
		
		
		$mensaje_flash = Yii::$app->session['mensaje_flash'];
		Yii::$app->session['mensaje_flash'] = '';
		
		return $this->render('/web/estadosmensajes/index', [
			'mensaje_flash' => $mensaje_flash,
		    
		    'listado_estados' => $listado_estados,
		    'listado_mensajes' => $listado_mensajes,
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
		$filtro_estado = isset($_POST['filtro_estado']) ? $_POST['filtro_estado'] : '';
		$filtro_mensaje = isset($_POST['filtro_mensaje']) ? $_POST['filtro_mensaje'] : '';
		
		$orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
		
		
		// PARA HACER EL FILTRADO EN EL MODELO
		$modelo = EstadosMensajes::find();
		

		if($filtro_id != '')
		{
			$modelo = $modelo->andWhere(['id' => $filtro_id]);
		}
		if($filtro_estado != '')
		{
			$modelo = $modelo->andWhere(['estado' => $filtro_estado]);
		}
		if($filtro_mensaje != '')
		{
			$modelo = $modelo->andWhere(['mensaje' => $filtro_mensaje]);
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
				'estado' => $modelo->estado0->estado,
				'mensaje' => $modelo->mensaje0->mensaje_esp,
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
		
		
		
		// PARA EL LISTADO DE ESTADOS
		$estados = Estados::find()
		->orderBy('id ASC')
		->all();
		
		$listado_estados = ['' => 'Seleccione una opcion'];
		$listado_estados = ArrayHelper::merge($listado_estados, ArrayHelper::map($estados, 'id', function ($elemento){
		    return $elemento->estado;
		}));
		
		
	    // PARA EL LISTADO DE MENSAJES
	    $mensajes = Mensajes::find()
	    ->orderBy('id ASC')
	    ->all();
	    
	    $listado_mensajes = ['' => 'Seleccione una opcion'];
	    $listado_mensajes = ArrayHelper::merge($listado_mensajes, ArrayHelper::map($mensajes, 'id', function ($elemento){
	        return $elemento->mensaje_esp;
	    }));
		
		
		
		$modelo = new EstadosMensajes();
		
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
					'web/estadosmensajes'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/estadosmensajes/agregar', [
			'modelo' => $modelo,
			'editar' => false,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
		    
		    'listado_estados' => $listado_estados,
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
		
		
		// PARA EL LISTADO DE ESTADOS
		$estados = Estados::find()
		->orderBy('id ASC')
		->all();
		
		$listado_estados = ['' => 'Seleccione una opcion'];
		$listado_estados = ArrayHelper::merge($listado_estados, ArrayHelper::map($estados, 'id', function ($elemento){
		    return $elemento->estado;
		}));
		    
		    
	    // PARA EL LISTADO DE MENSAJES
	    $mensajes = Mensajes::find()
	    ->orderBy('id ASC')
	    ->all();
	    
	    $listado_mensajes = ['' => 'Seleccione una opcion'];
	    $listado_mensajes = ArrayHelper::merge($listado_mensajes, ArrayHelper::map($mensajes, 'id', function ($elemento){
	        return $elemento->mensaje_esp;
	    }));
		
		
		$modelo = EstadosMensajes::find()->where(['id' => $id])->one();
		
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
					'web/estadosmensajes'
				])->send();
				return;
			}
			else
				$mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
		}
		
		return $this->render('/web/estadosmensajes/agregar', [
			'modelo' => $modelo,
			'editar' => true,
			'mensaje_error' => $mensaje_error,
			'mensaje_exito' => $mensaje_exito,
		    
		    'listado_estados' => $listado_estados,
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
			$modelo = EstadosMensajes::find()->where(['id' => $_POST['id']])->one();
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
	        $estado = $_POST['estado'];
	        $mensaje = $_POST['mensaje'];
	        
	        $modelo = new EstadosMensajes();
	        $modelo->estado = $estado;
	        $modelo->mensaje = $mensaje;
	        
	        if($modelo->save())
	        {
	            $json_respuesta = array(
	                'codigo' => '1',
	                'mensaje' => 'El mensaje se ha creado exitosamente...',
	            );
	        }
	        else
	        {
	            $json_respuesta = array(
	                'codigo' => '0',
	                'mensaje' => 'Ha ocurrido un error al intentar agregar el mensaje...',
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
	        $modelo = EstadosMensajes::find()->where(['id' => $_POST['id']])->one();
	        if($modelo != null)
	        {
	            $estado = $_POST['estado'];
	            $mensaje = $_POST['mensaje'];
	            
	            $modelo->estado = $estado;
	            $modelo->mensaje = $mensaje;
	            
	            if($modelo->save())
	            {
	                $json_respuesta = array(
	                    'codigo' => '1',
	                    'mensaje' => 'El mensaje se ha editado exitosamente...',
	                );
	            }
	            else
	            {
	                $json_respuesta = array(
	                    'codigo' => '0',
	                    'mensaje' => 'Ha ocurrido un error al intentar editar el mensaje...',
	                );
	            }
	        }
	        else
	        {
	            $json_respuesta = array(
	                'codigo' => '0',
	                'mensaje' => 'Ha ocurrido un error al intentar editar el mensaje...',
	            );
	        }
	    }
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
}
