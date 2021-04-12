<?php

namespace common\controllers;

use Yii;
use common\models\Notificaciones;
use common\models\NotificacionesEmails;
use common\models\Parametros;

class Utilidades
{

	public function generarEmails($para, $titulo, $notificacion_id, $valores)
	{
		$notificacion = Notificaciones::find()->where([
			'identificador' => $notificacion_id 
		])->one();
		
		$modelo = new NotificacionesEmails();
		$modelo->para = $para;
		$modelo->titulo = $titulo;
		$modelo->variables = json_encode($valores);
		$modelo->enviado = 0;
		$modelo->fecha_creacion = (new \DateTime())->format('Y-m-d H:i:s');
		$modelo->notificacion = $notificacion->id;
		
		$modelo->save();
		
		return $modelo->id;
	}

	public function enviarEmails()
	{
		$modelos = NotificacionesEmails::find()->where([
			'enviado' => 0, 
		])->orderBy('fecha_creacion DESC')->all();
		
		foreach($modelos as $modelo)
		{			
			$contenido_variables = $modelo->notificacion0->contenido;
			$variables = json_decode($modelo->variables, true);
			
			foreach($variables as $variable)
			{
				$contenido_variables = str_replace('%%' . $variable['nombre'] . '%%', $variable['valor'], $contenido_variables);
			}
			
			ob_start();
			echo Yii::$app->controller->renderPartial('/../../common/views/emails.html', [
				'titulo' => $modelo->titulo,
				'contenido' => $contenido_variables,
				
				'nombre_sitio' => Parametros::find()->where(['orden' => 1])->one()->valor,
				'url_sitio' => Parametros::find()->where(['orden' => 2])->one()->valor,
				'email_sitio' => Parametros::find()->where(['orden' => 3])->one()->valor,
				'color_fondo_cabecera' => '#000000',
				'color_linea_cabecera' => '#000000',
				'color_fondo_pie' => '#000000',
				'color_linea_pie' => '#000000',
				'color_texto_pie' => '#FFFFFF',
			]);
			$html_content = ob_get_contents();
			ob_end_clean();
			
			$desde = [Parametros::find()->where(['orden' => 4])->one()->valor => Parametros::find()->where(['orden' => 1])->one()->valor];
			
			Yii::$app->mailer->compose()
			->setFrom($desde)
			->setTo($modelo->para)
			->setSubject($modelo->titulo)
			->setTextBody($html_content)
			->setHtmlBody($html_content)
			->send();
			
			
			// UNA VEZ ENVIADO SE GUARDA LA NOTIFICACION
			$modelo->enviado = 1;
			$modelo->save();
		}
	}

	public function haIniciadoSesion()
	{
		if(isset(Yii::$app->session['usuario']))
		{
			if(Yii::$app->session['usuario'] != '')
				return true;
			else
				return false;
		}
		else
			return false;
	}

	public function haIniciadoSesionBackend()
	{
		if(isset(Yii::$app->session['backend_usuario']) && isset(Yii::$app->session['backend_tipo_usuario']))
			return true;
		else
			return false;
	}
	
	
	/* PARA LAS FUNCIONES PROPIAS DEL CHAT */
	public function filtrarPalabrasBuscar($query, $language)
	{
		$palabras_buscar = [];
		
		$array_palabras_query = explode(" ", $query);
		foreach($array_palabras_query as $palabra)
		{
			if($language == 'ESP')
			{
				$articulos_esp = $this->obtenerArticulosEsp();
				if(!in_array($palabra, $articulos_esp) && $palabra != '')
					$palabras_buscar[] = $palabra;
			}
			
			if($language == 'ENG')
			{
				$articulos_eng = $this->obtenerArticulosEng();				
				if(!in_array($palabra, $articulos_eng) && $palabra != '')
					$palabras_buscar[] = $palabra;
			}
			
			if($language == 'TUR')
			{
				$articulos_tur = $this->obtenerArticulosTur();				
				if(!in_array($palabra, $articulos_tur) && $palabra != '')
					$palabras_buscar[] = $palabra;
			}
		}
		
		return $palabras_buscar;
	}
	
	public function obtenerArticulosEsp()
	{
		return array(
			'el',
			'la',
			'de',
			'los',
			'las',
			'ella',
			'ellos',
			'ellas',
		);
	}
	
	public function obtenerArticulosEng()
	{
		return array(
			'the',
			'of',
			'at',
			'he',
			'she',
			'we',
			'they',
		);
	}
	
	public function obtenerArticulosTur()
	{
		return array(
			'the',
			'at',
			'he',
			'she',
			'we',
			'they',
		);
	}
}