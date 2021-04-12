<?php

namespace frontend\controllers;

use Yii;
use yii\web\Response;
use common\controllers\CController;
use common\models\web\Estados;
use common\controllers\Utilidades;
use common\models\web\Acciones;
use MongoDB;
use common\models\web\Medicos;
use common\models\web\Historiales;
use common\models\web\TraduccionesTurco;
use common\models\web\Libros;

class ApiV1Controller extends CController
{
	public $enableCsrfValidation = false;
	
	public function actionInit()
	{
		$language = $_POST['language'];
		$medico_id = $_POST['medico_id'];
		
		$json_respuesta = [];
		
		$estado_inicial = Estados::find()
		->andWhere(['tipo_estado' => 'Inicial'])
		->one();
		
		
		$array_messages = array();
		
		$mensaje = $estado_inicial->obtenerMensaje($language);
		$array_messages[] = $mensaje;
		
		// PARA EL IDENTIFICADOR
		$identificador = number_format((new \DateTime())->getTimestamp(), 0, '', '' );
		 
		$json_respuesta = array(
			'conversation_id' => $identificador,
		    'estate_id' => $estado_inicial->id,
			'estate_title' => $estado_inicial->estado,
			'estate_type' => $estado_inicial->tipo_estado,
			'messages' => $array_messages,
			'date_message' => (new \Datetime)->format('d/m/Y H:i:s'),
			'language' => $language,
			'have_buttons' => 'false',
		);
		
		
		// PARA COMENZAR LA CONVERSACION EN EL HISTORIAL
		$medico_obj = Medicos::find()
		->andWhere(['id' => $medico_id])
		->one();
		
		$historial = new Historiales();
		$historial->identificador = $identificador;
		$historial->tipo_mensaje = 'Chatbot';
		$historial->mensaje = $mensaje;
		$historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
		$historial->medico = $medico_obj->id;
		$historial->save();
		
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	public function actionSendMessage()
	{		
		// PARA LAS UTILIDADES
		$utilidades = new Utilidades();
		
		$json_respuesta = [];
		
		$language = $_POST['language'];
		$query = $_POST['query'];
		$current_estate = $_POST['currentEstate'];
		$medico_id = $_POST['medico_id'];
		$conversation_id = $_POST['conversation_id'];
		
		// PARA LOS ESTADOS POR DEFECTO
		$estado_inicial = Estados::find()->andWhere(['tipo_estado' => 'Inicial'])->one();
		$estado_final = Estados::find()->andWhere(['tipo_estado' => 'Final'])->one();
		$estado_desconocido = Estados::find()->andWhere(['tipo_estado' => 'Desconocido'])->one();
		$estado_sin_resultados = Estados::find()->andWhere(['tipo_estado' => 'Sin Resultados'])->one();
		$estado_seleccionado = null;
		
		
		
		// PARA GUARDAR EL MENSAJE DEL USUARIO EN EL HISTORIAL
		$medico_obj = Medicos::find()
		->andWhere(['id' => $medico_id])
		->one();
		
		$historial = new Historiales();
		$historial->identificador = $conversation_id;
		$historial->tipo_mensaje = 'Medico';
		$historial->mensaje = $query;
		$historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
		$historial->medico = $medico_obj->id;
		$historial->save();
		
		
		
		// PARA HACER LA RESPECTIVA TRADUCCION A TURCO SI EXISTE
		$new_query = '';
		$fue_traducido = false;
		if(!$fue_traducido)
		{
		    $traducciones_4_palabras = TraduccionesTurco::find()
		    ->andWhere(['palabras' => 4])
		    ->all();
		    
		    foreach($traducciones_4_palabras as $traduccion)
		    {
    		    if(strpos($query, $traduccion->frase) !== false && !$fue_traducido)
    		    {
    		        $new_query = str_replace($traduccion->frase, $traduccion->traduccion, $query);
    		        $fue_traducido = true;
    		        break;
    		    }
		    }
		}
		
		if(!$fue_traducido)
		{
		    $traducciones_3_palabras = TraduccionesTurco::find()
		    ->andWhere(['palabras' => 3])
		    ->all();
		    
		    foreach($traducciones_3_palabras as $traduccion)
		    {
		        if(strpos($query, $traduccion->frase) !== false && !$fue_traducido)
		        {
		            $new_query = str_replace($traduccion->frase, $traduccion->traduccion, $query);
		            $fue_traducido = true;
		            break;
		        }
		    }
		}
		
		if(!$fue_traducido)
		{
		    $traducciones_2_palabras = TraduccionesTurco::find()
		    ->andWhere(['palabras' => 2])
		    ->all();
		    
		    foreach($traducciones_2_palabras as $traduccion)
		    {
		        if(strpos($query, $traduccion->frase) !== false && !$fue_traducido)
		        {
		            $new_query = str_replace($traduccion->frase, $traduccion->traduccion, $query);
		            $fue_traducido = true;
		            break;
		        }
		    }
		}
		
		if(!$fue_traducido)
		{
		    $traducciones_1_palabras = TraduccionesTurco::find()
		    ->andWhere(['palabras' => 1])
		    ->all();
		    
		    foreach($traducciones_1_palabras as $traduccion)
		    {
		        if(strpos($query, $traduccion->frase) !== false && !$fue_traducido)
		        {
		            $new_query = str_replace($traduccion->frase, $traduccion->traduccion, $query);
		            $fue_traducido = true;
		            break;
		        }
		    }
		}
		
		if($fue_traducido == true)
		{
            Yii::warning('query: ' . $query);
            Yii::warning('new query: ' . $new_query);
		    
		    $query = $new_query;
		}
		
		
		$palabras_buscar = $utilidades->filtrarPalabrasBuscar($query, $language);
		
		$estados_busqueda_encontrados = false;
		$have_buttons = 'false';
		
		$array_estados = array();
		$array_messages = array();
		foreach($palabras_buscar as $palabra)
		{
			Yii::warning($palabra);
			
			if($language == 'ESP')
			{
				$estados_palabra = Estados::find()
				->andWhere(['like', 'palabras_claves_esp', '#' . $palabra])
				->all();
			}
			if($language == 'ENG')
			{
				$estados_palabra = Estados::find()
				->andWhere(['like', 'palabras_claves_eng', '#' . $palabra])
				->all();
			}
			if($language == 'TUR')
			{
				$estados_palabra = Estados::find()
				->andWhere(['like', 'palabras_claves_tur', '#' . $palabra])
				->all();
			}
			
			foreach($estados_palabra as $estado_temp)
			{
				if(isset($array_estados[$estado_temp->id]))
					$array_estados[$estado_temp->id] = $array_estados[$estado_temp->id] + 1;
				else
					$array_estados[$estado_temp->id] = 1;
			}
		}
		
		
		Yii::warning($array_estados);
		if(count($array_estados) > 0)
		{
			asort($array_estados, SORT_NUMERIC);
			Yii::warning("Antes de analizar");
			Yii::warning($array_estados);
			
			
			foreach($array_estados as $index => $estado)
			{
				$estado_obj = Estados::find()->andWhere(['id' => $index])->one();
				
				
				Yii::warning('Index: ' . $index . ' (' . $estado_obj->tipo_estado . ')');
				Yii::warning($array_estados[$index]);
				
				if($estado_obj->tipo_estado == "Busqueda"){
					$array_estados[$index] = $array_estados[$index] + 2;
				}
				
				Yii::warning('Index: ' . $index . ' (' . $estado_obj->tipo_estado . ')');
				Yii::warning($array_estados[$index]);
			}
			
			asort($array_estados, SORT_NUMERIC);
			Yii::warning("Despues de analizar");
			Yii::warning($array_estados);
			
			reset($array_estados);
			$estado_seleccionado = Estados::find()->andWhere(['id' => array_keys($array_estados)[count($array_estados) - 1]])->one();
			
			if($estado_seleccionado->tipo_estado == 'Busqueda')
			{
			    $condition = ['or'];
			    foreach($palabras_buscar as $palabra)
			    {
			        $condition[] = ['like', 'title', $palabra];
			        $condition[] = ['like', 'abstract', $palabra];
			        $condition[] = ['like', 'keywords', $palabra];
			    }
			    
			    
			    
			    
			    Yii::warning(Libros::find()->andWhere($condition)->createCommand()->getRawSql());
			    
			    $libros_bd = Libros::find()
			    ->andWhere($condition)
			    ->all();
			    
			    
			    if(count($libros_bd) > 0)
			    {
			        $array_resultados = array();

			        // PARA COPIAR LOS RESULTADOS EN UN ARRAY
			        foreach($libros_bd as $libro)
			        {
			            $array_resultados[] = array(
			                'title' => $libro->title,
			                'abstract' => $libro->abstract,
			                'author' => $libro->author,
			                'keywords' => $libro->keywords,
			                'file' => $libro->file,
			                'points' => 0,
			            );
			        }
			        
			        // PARA DARLE UNA PUNTUACION RESPECTIVA EN BASE A LAS COINCIDENCIAS
			        foreach($array_resultados as $index => $resultado)
			        {
			            $puntuacion = 0;
			            foreach($palabras_buscar as $palabra)
			            {
			                $puntuacion = $puntuacion + substr_count($resultado['title'], $palabra);
			                $puntuacion = $puntuacion + substr_count($resultado['abstract'], $palabra);
			                $puntuacion = $puntuacion + substr_count($resultado['keywords'], $palabra);
			            }
			            
			            $array_resultados[$index]['points'] = $puntuacion;
			        }
			        
			        // PARA ORDENAR LOS RESULTADOS DE MAYOR A MENOR
			        usort($array_resultados, function($a, $b) {
			            return $b['points'] - $a['points'];
			        });
			        
			        Yii::warning($array_resultados);
			        
			        
			        // PARA FILTRAR SOLO LOS RESULTADOS QUE TENGAN MAS DE 3 COINCIDENCIAS
			        $array_resultados_filtrado = array();
			        foreach($array_resultados as $result){
			            if($result['points'] >= 3)
			            {
			                $array_resultados_filtrado[] = $result;
			            }
			        }
			        
			        
			        // PARA FINALMENTE ENVIAR LOS RESULTADOS
			        if(count($array_resultados_filtrado) > 0)
			        {
    			        foreach($array_resultados_filtrado as $result){
    			            
    			            // PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
    			            $historial = new Historiales();
    			            $historial->identificador = $conversation_id;
    			            $historial->tipo_mensaje = 'Chatbot';
    			            $historial->mensaje = $result['title'] . '. ' . $result['abstract'];
    			            $historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
    			            $historial->medico = $medico_obj->id;
    			            $historial->save();
    			            
    			            
    			            $array_messages[] = array(
    			                'title' => $result['title'],
    			                'abstract' => $result['abstract'],
    			                'author' => $result['author'],
    			                'pdfName' => $result['file'],
    			                'pdfLink' => 'http://chatbot.tk/media/libros/' . $result['file'],
    			            );
    			        }
    			        
    			        $have_buttons = 'true';	
			        }
			        else
			        {
			            $estado_seleccionado = $estado_sin_resultados;
			            
			            $mensaje = $estado_seleccionado->obtenerMensaje($language);
			            $array_messages[] = $mensaje;
			            $have_buttons = 'false';
			            
			            
			            // PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
			            $historial = new Historiales();
			            $historial->identificador = $conversation_id;
			            $historial->tipo_mensaje = 'Chatbot';
			            $historial->mensaje = $mensaje;
			            $historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
			            $historial->medico = $medico_obj->id;
			            $historial->save();
			        }
			    }
			    else
			    {
			        $estado_seleccionado = $estado_sin_resultados;
			        
			        $mensaje = $estado_seleccionado->obtenerMensaje($language);
			        $array_messages[] = $mensaje;
			        $have_buttons = 'false';
			        
			        
			        // PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
			        $historial = new Historiales();
			        $historial->identificador = $conversation_id;
			        $historial->tipo_mensaje = 'Chatbot';
			        $historial->mensaje = $mensaje;
			        $historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
			        $historial->medico = $medico_obj->id;
			        $historial->save();
			    }
			    
			    
			    // ->andWhere(['or', ['like', 'web_personas.nombre', $filtro_titular], ['like', 'web_personas.apellido', $filtro_titular]]);
			    
			    
			    
			    /*************************************************************
			    INICIO DEL ELASTIC SEARCH ORIGINAL ***************************
			    **************************************************************
				
				
				// PARA HACER EL ELASTIC SEARCH
				$ch = curl_init();
				$method = "GET";
				$url = "http://desarrollo4.blendarsys.com:9200/zeki/document/_search";
				
				
				$string_query = '';
				foreach($palabras_buscar as $palabra)
				{
					if($string_query == '')
						$string_query = $palabra;
						else
							$string_query .= ' OR ' . $palabra;
				}
				
				$qry = '
				{
					"query": {
						"query_string" : {
					        "fields" : ["title", "abstract", "keywords"],
					        "query" : "' . $string_query . '"
					    }
					}
				}
				';
				
				
				Yii::warning("query for elastic search");
				Yii::warning($qry);
				
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $qry);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($qry))
						);
				
				$results_elastic_search = curl_exec($ch);
				curl_close($ch);
				
				$json_results_elastic_search = json_decode($results_elastic_search, true);
				
				
				
				try
				{
					if($json_results_elastic_search['hits']['total'] > 0)
					{
						foreach($json_results_elastic_search['hits']['hits'] as $result){
							
						    // PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL						    
						    $historial = new Historiales();
						    $historial->identificador = $conversation_id;
						    $historial->tipo_mensaje = 'Chatbot';
						    $historial->mensaje = $result['_source']['title'] . '. ' . $result['_source']['abstract'];
						    $historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
						    $historial->medico = $medico_obj->id;
						    $historial->save();
						    
						    
						    $array_messages[] = array(
								'title' => $result['_source']['title'],
								'abstract' => $result['_source']['abstract'],
								'author' => $result['_source']['author'],
								'pdfName' => $result['_source']['fileName'],
								'pdfLink' => 'blob:https://zeki.blendarsys.com/4be4675a-a2ca-47c2-acc2-94f7ac2f9c6f',
							);
						}
						
						$have_buttons = 'true';
					}
					else
					{
						$estado_seleccionado = $estado_sin_resultados;
						
						$mensaje = $estado_seleccionado->obtenerMensaje($language);									
						$array_messages[] = $mensaje;
						$have_buttons = 'false';
						
						
						// PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
						$historial = new Historiales();
						$historial->identificador = $conversation_id;
						$historial->tipo_mensaje = 'Chatbot';
						$historial->mensaje = $mensaje;
						$historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
						$historial->medico = $medico_obj->id;
						$historial->save();
					}
				}
				catch(\Exception $e)
				{
					$estado_seleccionado = $estado_sin_resultados;
					
					$mensaje = $estado_seleccionado->obtenerMensaje($language);								
					$array_messages[] = $mensaje;
					$have_buttons = 'false';
					
					// PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
					$historial = new Historiales();
					$historial->identificador = $conversation_id;
					$historial->tipo_mensaje = 'Chatbot';
					$historial->mensaje = $mensaje;
					$historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
					$historial->medico = $medico_obj->id;
					$historial->save();
				}
				
				*************************************************************
				FIN DEL ELASTIC SEARCH ORIGINAL *****************************
				************************************************************/
						
				
			}
			else
			{
				$estado_seleccionado = Estados::find()->andWhere(['id' => array_keys($array_estados)[count($array_estados) - 1]])->one();
				
				$mensaje = $estado_seleccionado->obtenerMensaje($language);
				$array_messages[] = $mensaje;
				
				// PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
				$historial = new Historiales();
				$historial->identificador = $conversation_id;
				$historial->tipo_mensaje = 'Chatbot';
				$historial->mensaje = $mensaje;
				$historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
				$historial->medico = $medico_obj->id;
				$historial->save();
			}
			
			
			
			/*
			
			$have_buttons = 'true';
			
			
			
			
			if($estados_busqueda_encontrados == false)
			{
				asort($array_estados, SORT_NUMERIC);
				Yii::warning($array_estados);
				
				reset($array_estados);
				$estado_seleccionado = Estados::find()->andWhere(['id' => array_keys($array_estados)[count($array_estados) - 1]])->one();
				
				if($estado_seleccionado->tipo_estado == 'Busqueda'){
					$have_buttons = 'true';
				}
				
				$mensaje = '';
				if($language == 'ESP')
					$mensaje = $estado_seleccionado->mensaje0->mensaje_esp;
				if($language == 'ENG')
					$mensaje = $estado_seleccionado->mensaje0->mensaje_eng;
				if($language == 'TUR')
					$mensaje = $estado_seleccionado->mensaje0->mensaje_tur;
							
				$array_messages[] = $mensaje;
			}
			else
			{
				asort($array_estados, SORT_NUMERIC);
				
				reset($array_estados);
				$estado_seleccionado = Estados::find()->andWhere(['id' => array_keys($array_estados)[count($array_estados) - 1]])->one();
				
				
				// PARA HACER EL ELASTIC SEARCH
				$ch = curl_init();
				$method = "GET";
				$url = "http://desarrollo4.blendarsys.com:9200/zeki/document/_search";
				
				
				$string_query = '';
				foreach($palabras_buscar as $palabra)
				{
					if($string_query == '')
						$string_query = $palabra;
					else
						$string_query .= ' OR ' . $palabra;
				}
				
				$qry = '
				{
					"query": { 					
						"query_string" : {
					        "fields" : ["title", "abstract", "keywords"],
					        "query" : "' . $string_query . '"
					    }
					}
				}
				';
				
				
				Yii::warning("query for elastic search");
				Yii::warning($qry);
				
				
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $qry);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($qry))
				); 
				
				$results_elastic_search = curl_exec($ch);
				curl_close($ch);
				
				$json_results_elastic_search = json_decode($results_elastic_search, true);
				
				
				
				if($json_results_elastic_search['hits']['total'] > 0)
				{
					foreach($json_results_elastic_search['hits']['hits'] as $result){
						$array_messages[] = array(
							'title' => $result['_source']['title'],
							'abstract' => $result['_source']['abstract'],
							'author' => $result['_source']['author'],
							'pdfName' => $result['_source']['fileName'],
							'pdfLink' => 'blob:https://zeki.blendarsys.com/4be4675a-a2ca-47c2-acc2-94f7ac2f9c6f',
						);
					}
				}
				else
				{
					$estado_seleccionado = $estado_sin_resultados;
					
					$mensaje = '';
					if($language == 'ESP')
						$mensaje = $estado_seleccionado->mensaje0->mensaje_esp;
					if($language == 'ENG')
						$mensaje = $estado_seleccionado->mensaje0->mensaje_eng;
					if($language == 'TUR')
						$mensaje = $estado_seleccionado->mensaje0->mensaje_tur;
								
					$array_messages[] = $mensaje;
					$have_buttons = 'false';
				}
			}
			
			*/
			
		}
		else
		{
			$estado_seleccionado = $estado_desconocido;
			
			$mensaje = $estado_seleccionado->obtenerMensaje($language);
			$array_messages[] = $mensaje;
			
			
			// PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
			$historial = new Historiales();
			$historial->identificador = $conversation_id;
			$historial->tipo_mensaje = 'Chatbot';
			$historial->mensaje = $mensaje;
			$historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
			$historial->medico = $medico_obj->id;
			$historial->save();
		}

		
			
			
			
		$json_respuesta = array(
			'estate_id' => $estado_seleccionado->id,
			'estate_title' => $estado_seleccionado->estado,
			'estate_type' => $estado_seleccionado->tipo_estado,
			'messages' => $array_messages,
			'date_message' => (new \Datetime)->format('d/m/Y H:i:s'),
			'language' => $language,
			'have_buttons' => $have_buttons,
		);
		
		Yii::$app->response->format = Response::FORMAT_JSON;
		return $json_respuesta;
	}
	
	/*
	public function actionDownloadFile($id)
	{
		$pdfName = '';
		
		
		// PARA HACER EL ELASTIC SEARCH
		$ch = curl_init();
		$method = "GET";
		$url = "http://desarrollo4.blendarsys.com:9200/zeki/document/_search";
		
		$qry = '
		{
			"query": {
				"query_string" : {
			        "fields" : ["id"],
			        "query" : "' . $id . '"
			    }
			}
		}
		';		
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $qry);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($qry))
		);
		
		$results_elastic_search = curl_exec($ch);
		curl_close($ch);
		
		$json_results_elastic_search = json_decode($results_elastic_search, true);
		

		if($json_results_elastic_search['hits']['total'] > 0)
		{
			foreach($json_results_elastic_search['hits']['hits'] as $result){
				$pdfName = $result['_source']['fileName'];
			}
			
			
			$directorio = __DIR__ . '/../../www/media/imagenes/';
			if(!file_exists($directorio)){
				mkdir($directorio, 0777, true);
			}
			
			
			$user_bd = 'root';
			$pass_bd = 'Bl3nd4rsys';
			
			$client = new MongoDB\Client("mongodb://172.17.0.4:27017/zeki");
			$collection = $client->demo->beers;
			
			
			
			$result = $collection->insertOne( [ 'name' => 'Hinterland', 'brewery' => 'BrewDog' ] );
			
			var_dump($result);
			
			/*
			
			// GridFS
			$grid = $db->getGridFS();
			
			// The file's location in the File System
			$path = "/repo/pdf/b20e920ab64e6c67982e35c92745e083";
			
			$filename = "b20e920ab64e6c67982e35c92745e083.pdf";
			
			// Note metadata field & filename field
			$storedfile = $grid->storeFile(
			    $path . $filename,
			    array(
			        "metadata" => array("filename" => $filename),
			        "filename" => $filename
			    )
            );
			    
			    
			// Return newly stored file's Document ID
			echo $storedfile;
			
			
			// file_put_contents($directorio . $pdfName, file_get_contents('blob:https://zeki.blendarsys.com/' . $id));
			
			
			header("Content-length: " . $result['pdf_size']);
			header("Content-type: application/pdf");
			header("Content-Disposition: attachment; filename=".$result['pdf_name']);
			
			echo $storedfile;
			*****
		}
		else
		{
			$json_respuesta = array(
				'message' => 'File not found...'
			);
			
			Yii::$app->response->format = Response::FORMAT_JSON;
			return $json_respuesta;
		}
		
	}
	*/
	
	public function actionUserInactive()
	{
	    $language = $_POST['language'];
	    $medico_id = $_POST['medico_id'];
	    $conversation_id = $_POST['conversation_id'];
	    
	    $json_respuesta = [];
	    
	    $estado_inactivo = Estados::find()
	    ->andWhere(['tipo_estado' => 'Inactivo'])
	    ->one();
	    
	    
	    $array_messages = array();
	    
	    $mensaje = $estado_inactivo->obtenerMensaje($language);
	    $array_messages[] = $mensaje;
	    
	    $json_respuesta = array(
	        'estate_id' => $estado_inactivo->id,
	        'estate_title' => $estado_inactivo->estado,
	        'estate_type' => $estado_inactivo->tipo_estado,
	        'messages' => $array_messages,
	        'date_message' => (new \Datetime)->format('d/m/Y H:i:s'),
	        'language' => $language,
	        'have_buttons' => 'false',
	    );
	    
	    
	    // PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
	    $medico_obj = Medicos::find()
	    ->andWhere(['id' => $medico_id])
	    ->one();
	    
	    $historial = new Historiales();
	    $historial->identificador = $conversation_id;
	    $historial->tipo_mensaje = 'Chatbot';
	    $historial->mensaje = $mensaje;
	    $historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
	    $historial->medico = $medico_obj->id;
	    $historial->save();
	    
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
	
	public function actionUserGoodbye()
	{
	    $language = $_POST['language'];
	    $medico_id = $_POST['medico_id'];
	    $conversation_id = $_POST['conversation_id'];
	    
	    $json_respuesta = [];
	    
	    $estado_final = Estados::find()
	    ->andWhere(['tipo_estado' => 'Final'])
	    ->one();
	    
	    
	    $array_messages = array();
	    
	    $mensaje = $estado_final->obtenerMensaje($language);
	    $array_messages[] = $mensaje;
	    
	    $json_respuesta = array(
	        'estate_id' => $estado_final->id,
	        'estate_title' => $estado_final->estado,
	        'estate_type' => $estado_final->tipo_estado,
	        'messages' => $array_messages,
	        'date_message' => (new \Datetime)->format('d/m/Y H:i:s'),
	        'language' => $language,
	        'have_buttons' => 'false',
	    );
	    
	    
	    // PARA GUARDAR EL MENSAJE DEL CHATBOT EN EL HISTORIAL
	    $medico_obj = Medicos::find()
	    ->andWhere(['id' => $medico_id])
	    ->one();
	    
	    $historial = new Historiales();
	    $historial->identificador = $conversation_id;
	    $historial->tipo_mensaje = 'Chatbot';
	    $historial->mensaje = $mensaje;
	    $historial->fecha_mensaje = (new \DateTime())->format('Y-m-d H:i:s');
	    $historial->medico = $medico_obj->id;
	    $historial->save();
	    
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
	
	public function actionGetKeywordsElasticSearch()
	{

		// PARA HACER EL ELASTIC SEARCH
		$ch = curl_init();
		$method = "GET";
		$url = "http://desarrollo4.blendarsys.com:9200/zeki/document/_search";
		
		$qry = '
		{
		}
		';
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $qry);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($qry))
				);
		
		$results_elastic_search = curl_exec($ch);
		curl_close($ch);
		
		$json_results_elastic_search = json_decode($results_elastic_search, true);
		
		
		if($json_results_elastic_search['hits']['total'] > 0)
		{
			$estado_buscar = Estados::find()
			->andWhere(['tipo_estado' => 'Busqueda'])
			->one();
			
			$array_final = array();
			$array_palabras_eng = \explode(' ', $estado_buscar->palabras_claves_eng);
			foreach($array_palabras_eng as $palabra)
			{
				$array_final[] = substr($palabra, 1);
			}
			
			
			foreach($json_results_elastic_search['hits']['hits'] as $result){
				$palabras_elastic_search = \explode(', ', $result['_source']['keywords']);
				
				foreach($palabras_elastic_search as $palabra_es){
					if(!in_array($palabra_es, $array_final, true)){
						$array_final[] = $palabra_es;
					}
				}
			}
			
			$palabras_string = '';
			foreach($array_final as $palabra)
			{
				if($palabras_string == '')
					$palabras_string = '#' . $palabra;
				else
					$palabras_string = $palabras_string . ' ' . '#' . $palabra;
			}
			
			
			$estado_buscar->palabras_claves_eng = $palabras_string;
			$estado_buscar->save();
			
			
			$json_respuesta = array(
				'message' => 'Palabras claves creadas correctamente',
			);
			
			Yii::$app->response->format = Response::FORMAT_JSON;
			return $json_respuesta;
			
		}
		else
		{
			$json_respuesta = array(
				'message' => 'File not found...'
			);
			
			Yii::$app->response->format = Response::FORMAT_JSON;
			return $json_respuesta;
		}
		
	}
	
	public function actionGetDocumentsElasticSearch()
	{
	    
	    // PARA HACER EL ELASTIC SEARCH
	    $ch = curl_init();
	    $method = "GET";
	    $url = "http://desarrollo4.blendarsys.com:9200/zeki/document/_search";
	    
	    $qry = '
		{
            "from" : 0, 
            "size" : 100,
            "query": {
                "match_all": {}
            }
		}
		';
	    
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $qry);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	        'Content-Type: application/json',
	        'Content-Length: ' . strlen($qry))
	        );
	    
	    $results_elastic_search = curl_exec($ch);
	    curl_close($ch);
	    
	    $json_results_elastic_search = json_decode($results_elastic_search, true);
	    
	    
	    if($json_results_elastic_search['hits']['total'] > 0)
	    {
	        $registros = array();
	        
	        foreach($json_results_elastic_search['hits']['hits'] as $result)
	        {	            
	            try
	            {
    	            $libro_obj = new Libros();
    	            $libro_obj->title = $result['_source']['title'];
    	            $libro_obj->author = $result['_source']['author'];
    	            $libro_obj->keywords = $result['_source']['keywords'];
    	            $libro_obj->magazine = $result['_source']['magazine'];
    	            $libro_obj->publication_date = $result['_source']['publicationDate'];
    	            $libro_obj->abstract = $result['_source']['abstract'];
    	            $libro_obj->content = $result['_source']['content'];
    	            $libro_obj->bibliography = $result['_source']['bibliography'];
    	            $libro_obj->file = $result['_source']['fileName'];
    	            
    	            if($libro_obj->save() == true)
    	            {
    	                $registros[] = array(
    	                    'source' => $result['_source'],
    	                    'mensaje' => 'Registrado',
    	                );
    	            }
    	            else
    	            {
    	                $registros[] = array(
    	                    'source' => $result['_source'],
    	                    'mensaje' => $libro_obj->getFirstErrors(),
    	                );
    	            }
	            }
	            catch(\Exception $e)
	            {
	                $registros[] = array(
	                    'source' => $result['_source'],
	                    'mensaje' => $e->getMessage(),
	                );
	            }
	        }
	        
	        
	        $json_respuesta = array(
	            'message' => 'Documentos creados exitosamente...',
	            'registros' => $registros,
	        );
	        
	        Yii::$app->response->format = Response::FORMAT_JSON;
	        return $json_respuesta;
	        
	    }
	    else
	    {
	        $json_respuesta = array(
	            'message' => 'URL not found...'
	        );
	        
	        Yii::$app->response->format = Response::FORMAT_JSON;
	        return $json_respuesta;
	    }
	    
	}
	
	public function actionLogin()
	{
	    $json_respuesta = [];
	    
	    $language = $_POST['language'];
	    $usuario = $_POST['usuario'];
	    $clave = $_POST['clave'];
	    
	    $medico_obj = Medicos::find()
	    ->andWhere(['usuario' => $usuario])
	    ->andWhere(['clave' => sha1($clave)])
	    ->andWhere(['eliminado' => false])
	    ->one();
	    
	    if($medico_obj != null)
	    {
	        $mensaje = '';
	        if($language == 'en')
	            $mensaje = 'Welcome ' . $medico_obj->nombre . ' ' . $medico_obj->apellido;
	        if($language == 'tr')
	            $mensaje = 'Hoşgeldin ' . $medico_obj->nombre . ' ' . $medico_obj->apellido;
	        
	        $json_respuesta = array(
	            'codigo' => '1',
	            'mensaje' => $mensaje,
	            'datos' => array(
	                'id' => $medico_obj->id,
	                'nombre' => $medico_obj->nombre . ' ' . $medico_obj->apellido,
	                'usuario' => $medico_obj->usuario,
	            ),
	        );
	    }
	    else
	    {
	        $mensaje = '';
	        if($language == 'en')
                $mensaje = 'The user or password entered does not exist in our database.';
            if($language == 'tr')
                $mensaje = 'Girilen kullanıcı veya şifre veritabanımızda mevcut değil.';
	        
	        $json_respuesta = array(
	            'codigo' => '0',
	            'mensaje' => $mensaje,
	        );
	    }
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
	
	public function actionForgotPassword()
	{
	    $json_respuesta = [];
	    
	    $language = $_POST['language'];
	    $email = $_POST['email'];
	    
	    $medico_obj = Medicos::find()
	    ->andWhere(['email' => $email])
	    ->andWhere(['eliminado' => false])
	    ->one();
	    
	    if($medico_obj != null)
	    {
	        $nueva_clave = substr(sha1($medico_obj->clave), 0, 6);
	        
	        $medico_obj->clave = sha1($nueva_clave);
	        $medico_obj->save();
	        
	        
	        
	        $utilidades = new Utilidades();
	        
	        // PARA GENERAR EL CORREO DE RECUPERACION DE CONTRASEÑA
	        $titulo = '';
	        $email = '';
	        if($language == 'en')
	        {
	            $titulo = 'Your password has been recovered...';
	            $email = 'recuperar_contrasena_cliente';
	        }
            if($language == 'tr')
            {
                $titulo = 'Şifreniz kurtarıldı ...';
                $email = 'recuperar_contrasena_cliente';
            }
	        
	        $valores = [];
	        array_push($valores, ['nombre' => 'nombre', 'valor' => $medico_obj->nombre . ' ' . $medico_obj->apellido]);
	        array_push($valores, ['nombre' => 'usuario', 'valor' => $medico_obj->usuario]);
	        array_push($valores, ['nombre' => 'email', 'valor' => $medico_obj->email]);
	        array_push($valores, ['nombre' => 'clave_nueva', 'valor' => $nueva_clave]);
	        $utilidades->generarEmails($medico_obj->email, $titulo, $email, $valores);
	        
	        
	        // PARA ENVIAR TODOS LOS CORREOS
	        $utilidades->enviarEmails();
	        
	        $mensaje = '';
	        if($language == 'en')
	            $mensaje = 'Your password has been recovered. You will can access with the new password sent to your email.';
            if($language == 'tr')
                $mensaje = 'Şifreniz kurtarıldı. E-postanıza gönderilen yeni şifreyle erişebilirsiniz.';
	        
	        $json_respuesta = array(
	            'codigo' => '1',
	            'mensaje' => $mensaje,
	        );
	    }
	    else
	    {
	        $mensaje = '';
	        if($language == 'en')
	            $mensaje = 'The email entered does not exist in our database.';
            if($language == 'tr')
                $mensaje = 'Girilen e-posta veritabanımızda mevcut değil.';
	        
	        $json_respuesta = array(
	            'codigo' => '0',
	            'mensaje' => $mensaje,
	        );
	    }
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
	
	
	public function actionRegisterUser()
	{
	    $json_respuesta = [];
	    
	    $language = $_POST['language'];
	    $nombre = $_POST['nombre'];
	    $apellido = $_POST['apellido'];
	    $usuario = $_POST['usuario'];
	    $email = $_POST['email'];
	    $clave = $_POST['clave'];
	    
	    $medicos_bd = Medicos::find()
	    ->andWhere(['email' => $email])
	    ->orWhere(['usuario' => $usuario])
	    ->andWhere(['eliminado' => false])
	    ->all();
	    
	    $medicos_bd_sql = Medicos::find()
	    ->andWhere(['email' => $email])
	    ->orWhere(['usuario' => $usuario])
	    ->andWhere(['eliminado' => false]);
	    Yii::warning($medicos_bd_sql->createCommand()->getRawSql());
	    
	    if(count($medicos_bd) == 0)
	    {
	        $medico_obj = new Medicos();
	        $medico_obj->codigo = "1";
	        $medico_obj->nombre = $nombre;
	        $medico_obj->apellido = $apellido;
	        $medico_obj->especialidad = "Medicina General";
	        $medico_obj->usuario = $usuario;
	        $medico_obj->email = $email;
	        $medico_obj->clave = sha1($clave);
	        $medico_obj->fecha_registro = (new \DateTime())->format("Y-m-d H:i:s");
	        $medico_obj->eliminado = 0;
	        
	        
	        if($medico_obj->save() == 1)
	        {
	            
	            try 
	            {
	                $utilidades = new Utilidades();
	                
	                // PARA GENERAR EL CORREO DE RECUPERACION DE CONTRASEÑA
	                $titulo = '';
	                if($language == 'en')
	                    $titulo = 'The user has successfully registered...';
                    if($language == 'tr')
                        $titulo = 'Kullanıcı başarıyla kayıt oldu...';
	                
	                
	                $valores = [];
	                array_push($valores, ['nombre' => 'nombre', 'valor' => $medico_obj->nombre . ' ' . $medico_obj->apellido]);
	                array_push($valores, ['nombre' => 'usuario', 'valor' => $medico_obj->usuario]);
	                array_push($valores, ['nombre' => 'email', 'valor' => $medico_obj->email]);
	                array_push($valores, ['nombre' => 'clave_nueva', 'valor' => $clave]);
	                $utilidades->generarEmails($medico_obj->email, $titulo, "registro_cliente", $valores);
	                
	                
	                // PARA ENVIAR TODOS LOS CORREOS
	                $utilidades->enviarEmails();
	            }
	            catch (\Exception $e) 
	            {
	                   
	            }
	            
	            
	            $mensaje = '';
	            if($language == 'en')
	                $mensaje = 'The user has successfully registered. You can now access your account with the username and password entered.';
                if($language == 'tr')
                    $mensaje = 'Kullanıcı başarıyla kayıt oldu. Artık hesabınıza girilen kullanıcı adı ve şifre ile erişebilirsiniz.';
	            
	            $json_respuesta = array(
	                'codigo' => '1',
	                'mensaje' => $mensaje,
	            );
	        }
	        else
	        {
	            $mensaje = '';
	            if($language == 'en')
	                $mensaje = 'An unexpected error occurred when trying to save the user to our database. Please, contact our technical support team to solve the problem.';
                if($language == 'tr')
                    $mensaje = 'Kullanıcıyı veritabanımıza kaydetmeye çalışırken beklenmeyen bir hata oluştu. Lütfen sorunu çözmek için teknik destek ekibimizle iletişim kurun.';
	            
	            $json_respuesta = array(
	                'codigo' => '0',
	                'mensaje' => $mensaje,
	            );
	        }	       
	    }
	    else
	    {
	        $mensaje = '';
	        if($language == 'en')
	            $mensaje = 'The email and/or the user entered already exist in our database. Please change them and try again';
            if($language == 'tr')
                $mensaje = 'Girilen e-posta ve / veya kullanıcı veritabanımızda zaten mevcut. Lütfen onları değiştirin ve tekrar deneyin';
	        
	        $json_respuesta = array(
	            'codigo' => '0',
	            'mensaje' => $mensaje,
	        );
	    }
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
	
	
	public function actionEditUser()
	{
	    $json_respuesta = [];
	    
	    $language = $_POST['language'];
	    $id = $_POST['id'];
	    $nombre = $_POST['nombre'];
	    $apellido = $_POST['apellido'];
	    $usuario = $_POST['usuario'];
	    $email = $_POST['email'];
	    $clave = $_POST['clave'];
	    
	    $medicos_bd = Medicos::find()
	    ->andWhere(['email' => $email])
	    ->orWhere(['usuario' => $usuario])
	    ->andWhere(['eliminado' => false])
	    ->all();
	    
	    $medico_obj = Medicos::find()->andWhere(['id' => $id])->one();
	    
	    
	    $debe_editar = true;
	    foreach($medicos_bd as $medico_bd)
	    {
	        if($medico_bd->id != $medico_obj->id)
	        {
	            $debe_editar = false;
	        }
	    }
	    
	    /*
	    $medicos_bd_sql = Medicos::find()
	    ->andWhere(['email' => $email])
	    ->orWhere(['usuario' => $usuario])
	    ->andWhere(['eliminado' => false]);
	    Yii::warning($medicos_bd_sql->createCommand()->getRawSql());
	    */
	    
	    if($debe_editar == true)
	    {
	        $medico_obj->codigo = "1";
	        $medico_obj->nombre = $nombre;
	        $medico_obj->apellido = $apellido;
	        $medico_obj->especialidad = "Medicina General";
	        $medico_obj->usuario = $usuario;
	        $medico_obj->email = $email;
	        
	        if($clave != '')
	        {
                $medico_obj->clave = sha1($clave);
	        }
	        
	        
	        $medico_obj->eliminado = 0;
	        
	        
	        if($medico_obj->save() == 1)
	        {
	            $mensaje = '';
	            if($language == 'en')
	                $mensaje = 'The user has successfully edited. You can now access your account with the username and password entered.';
                if($language == 'tr')
                    $mensaje = 'Kullanıcı başarıyla düzenledi. Artık hesabınıza girilen kullanıcı adı ve şifre ile erişebilirsiniz.';
	            
	            $json_respuesta = array(
	                'codigo' => '1',
	                'mensaje' => $mensaje,
	            );
	        }
	        else
	        {
	            $mensaje = '';
	            if($language == 'en')
	                $mensaje = 'An unexpected error occurred when trying to save the user to our database. Please, contact our technical support team to solve the problem.';
                if($language == 'tr')
                    $mensaje = 'Kullanıcıyı veritabanımıza kaydetmeye çalışırken beklenmeyen bir hata oluştu. Lütfen sorunu çözmek için teknik destek ekibimizle iletişim kurun.';
	            
	            $json_respuesta = array(
	                'codigo' => '0',
	                'mensaje' => $mensaje,
	            );
	        }
	    }
	    else
	    {
	        $mensaje = '';
	        if($language == 'en')
	            $mensaje = 'The email and/or the user entered already exist in our database. Please change them and try again';
            if($language == 'tr')
                $mensaje = 'Girilen e-posta ve / veya kullanıcı veritabanımızda zaten mevcut. Lütfen onları değiştirin ve tekrar deneyin';
	        
	        $json_respuesta = array(
	            'codigo' => '0',
	            'mensaje' => $mensaje,
	        );
	    }
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
	
	
	public function actionGetInfoUser()
	{
	    $id= $_POST['id'];
	    
	    $medico_obj = Medicos::find()->andWhere(['id' => $id])->one();
	    
	    $json_respuesta = array(
	        'id' => $medico_obj->id,
	        'nombre' => $medico_obj->nombre,
	        'apellido' => $medico_obj->apellido,
	        'email' => $medico_obj->email,
	        'usuario' => $medico_obj->usuario,
	    );
	    
	    
	    Yii::$app->response->format = Response::FORMAT_JSON;
	    return $json_respuesta;
	}
}
