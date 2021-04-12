<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\Libros;

class LibrosController extends CController
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
        
        return $this->render('/web/libros/index', [
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
        $filtro_title = isset($_POST['filtro_title']) ? $_POST['filtro_title'] : '';
        $filtro_author = isset($_POST['filtro_author']) ? $_POST['filtro_author'] : '';
        $filtro_keywords = isset($_POST['filtro_keywords']) ? $_POST['filtro_keywords'] : '';
        $filtro_publication_date = isset($_POST['filtro_publication_date']) ? $_POST['filtro_publication_date'] : '';
        
        $orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
        
        
        // PARA HACER EL FILTRADO EN EL MODELO
        $modelo = Libros::find();
        
        if($filtro_id != '')
        {
            $modelo = $modelo->andWhere(['id' => $filtro_id]);
        }
        if($filtro_title != '')
        {
            $modelo = $modelo->andWhere(['like', 'title', $filtro_title]);
        }
        if($filtro_author != '')
        {
            $modelo = $modelo->andWhere(['like', 'author', $filtro_author]);
        }
        if($filtro_keywords != '')
        {
            $modelo = $modelo->andWhere(['like', 'keywords', $filtro_keywords]);
        }
        if($filtro_publication_date != '')
        {
            $fecha_minima = date_create_from_format('d/m/Y H:i:s', $filtro_publication_date . ' 00:00:00');
            $fecha_maxima = date_create_from_format('d/m/Y H:i:s', $filtro_publication_date . ' 23:59:59');
            
            $modelo = $modelo->andWhere(['>=', 'publication_date', $fecha_minima->format('Y-m-d H:i:s')]);
            $modelo = $modelo->andWhere(['<=', 'publication_date', $fecha_maxima->format('Y-m-d H:i:s')]);
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
            $publication_date = '';
            if($modelo->publication_date != null || $modelo->publication_date != ''){
                $publication_date = (date_create_from_format('Y-m-d', $modelo->publication_date))->format('d/m/Y');
            }
            
            $listado_modelos_array[] = [
                'id' => $modelo->id,
                'title' => $modelo->title,
                'author' => $modelo->author,
                'keywords' => $modelo->keywords,
                'publication_date' => $publication_date,
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
        
        
        $modelo = new Libros();
        
        $mensaje_error = '';
        $mensaje_exito = '';
        if(Yii::$app->request->isPost)
        {
            $modelo->load(Yii::$app->request->post());
            
            if($modelo->validate())
            {
                $publication_date = null;
                if($modelo->publication_date != '')
                {
                    $publication_date = ((new \DateTime())->createFromFormat('d/m/Y', $modelo->publication_date))->format('Y-m-d');
                }
                
                $nombre_archivo = $this->guardar_archivo_subida($_FILES['Libros']);
                
                $modelo->file = $nombre_archivo['nombre_archivo'];
                $modelo->publication_date = $publication_date;
                $modelo->save();
                
                
                $mensaje_exito = 'El Libro ha sido ingresado exitosamente.';
                Yii::$app->session['mensaje_flash'] = $mensaje_exito;
                
                Yii::$app->getResponse()->redirect([
                    'web/libros'
                ])->send();
                return;
            }
            else
                $mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
        }
        
        return $this->render('/web/libros/agregar', [
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
        
        $modelo = Libros::find()->where(['id' => $id])->one();
        
        $file_viejo = $modelo->file;
        
        $mensaje_error = '';
        $mensaje_exito = '';
        if(Yii::$app->request->isPost)
        {
            $modelo->load(Yii::$app->request->post());
            
            if($modelo->validate())
            {
                $publication_date = null;
                if($modelo->publication_date != '')
                {
                    $publication_date = ((new \DateTime())->createFromFormat('d/m/Y', $modelo->publication_date))->format('Y-m-d');
                }
                
                if(isset($_FILES['Libros']))
                {
                    if($_FILES['Libros']['name']['file'] != '')
                    {
                        $nombre_archivo = $this->guardar_archivo_subida($_FILES['Libros']);
                        $modelo->file = $nombre_archivo['nombre_archivo'];
                    }
                    else
                    {
                        $modelo->file = $file_viejo;
                    }
                }
                else
                {
                    $modelo->file = $file_viejo;
                }
                
                $modelo->publication_date = $publication_date;
                $modelo->save();
                
                
                $mensaje_exito = 'El Libro ha sido editado exitosamente.';
                Yii::$app->session['mensaje_flash'] = $mensaje_exito;
                
                Yii::$app->getResponse()->redirect([
                    'web/libros'
                ])->send();
                return;
            }
            else
                $mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
        }
        
        return $this->render('/web/libros/agregar', [
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
            $modelo = Libros::find()->where(['id' => $_POST['id']])->one();
            if($modelo != null)
            {
                $modelo->eliminado = true;
                $modelo->save();
                
                $json_respuesta = array(
                    'codigo' => '1',
                    'mensaje' => 'El libro se ha eliminado exitosamente...',
                );
            }
            else
            {
                $json_respuesta = array(
                    'codigo' => '0',
                    'mensaje' => 'Ha ocurrido un error al intentar eliminar el libro...',
                );
            }
        }
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $json_respuesta;
    }
    
    private function guardar_archivo_subida($f)
    {
        $directorio = __DIR__ . '/../../../www/media/libros/';
        if(!file_exists($directorio)){
            mkdir($directorio, 0777, true);
        }
        
        $nombre_archivo = $f['name']['file'];
        $nombre_archivo_solo = 'n/a';
        $nombre_archivo_thumb = 'n/a';
        
        
        move_uploaded_file($f["tmp_name"]['file'], $directorio . basename($nombre_archivo));
        
        return array(
            'nombre_archivo' => $nombre_archivo,
            'nombre_archivo_thumb' => $nombre_archivo_thumb,
            'nombre_archivo_solo' => $nombre_archivo_solo,
        );
    }
}
