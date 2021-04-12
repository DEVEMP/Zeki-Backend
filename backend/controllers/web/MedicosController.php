<?php

namespace backend\controllers\web;

use Yii;
use common\controllers\CController;
use common\controllers\Utilidades;

use yii\data\Pagination;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use common\models\Parametros;
use common\models\web\Medicos;

class MedicosController extends CController
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
        
        return $this->render('/web/medicos/index', [
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
        
        $filtro_codigo = isset($_POST['filtro_codigo']) ? $_POST['filtro_codigo'] : '';
        $filtro_nombre = isset($_POST['filtro_nombre']) ? $_POST['filtro_nombre'] : '';
        $filtro_apellido = isset($_POST['filtro_apellido']) ? $_POST['filtro_apellido'] : '';
        $filtro_especialidad = isset($_POST['filtro_especialidad']) ? $_POST['filtro_especialidad'] : '';
        $filtro_email = isset($_POST['filtro_email']) ? $_POST['filtro_email'] : '';
        $filtro_usuario = isset($_POST['filtro_usuario']) ? $_POST['filtro_usuario'] : '';
        
        $orden_campo = isset($_POST['orden_campo']) ? $_POST['orden_campo'] : 'id ASC';
        
        
        // PARA HACER EL FILTRADO EN EL MODELO
        $modelo = Medicos::find();
        
        $modelo = $modelo->andWhere(['eliminado' => false]);
        
        if($filtro_codigo != '')
        {
            $modelo = $modelo->andWhere(['codigo' => $filtro_codigo]);
        }
        if($filtro_nombre != '')
        {
            $modelo = $modelo->andWhere(['like', 'nombre', $filtro_nombre]);
        }
        if($filtro_apellido != '')
        {
            $modelo = $modelo->andWhere(['like', 'apellido', $filtro_apellido]);
        }
        if($filtro_especialidad != '')
        {
            $modelo = $modelo->andWhere(['especialidad' => $filtro_especialidad]);
        }
        if($filtro_email != '')
        {
            $modelo = $modelo->andWhere(['like', 'email', $filtro_email]);
        }
        if($filtro_usuario != '')
        {
            $modelo = $modelo->andWhere(['like', 'usuario', $filtro_email]);
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
                'codigo' => $modelo->codigo,
                'nombre' => $modelo->nombre,
                'apellido' => $modelo->apellido,
                'especialidad' => $modelo->especialidad,
                'email' => $modelo->email,
                'usuario' => $modelo->usuario,
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
        
        
        $modelo = new Medicos();
        
        $mensaje_error = '';
        $mensaje_exito = '';
        if(Yii::$app->request->isPost)
        {
            $modelo->load(Yii::$app->request->post());
            
            if($modelo->validate())
            {
                $clave = $modelo->clave;
                $modelo->clave = sha1($modelo->clave);
                
                $modelo->save();
                
                
                $mensaje_exito = 'El Medico ha sido ingresado exitosamente.';
                Yii::$app->session['mensaje_flash'] = $mensaje_exito;
                
                Yii::$app->getResponse()->redirect([
                    'web/medicos'
                ])->send();
                return;
            }
            else
                $mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
        }
        
        return $this->render('/web/medicos/agregar', [
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
        
        $modelo = Medicos::find()->where(['id' => $id])->one();
        
        $clave_anterior = $modelo->clave;
        
        $mensaje_error = '';
        $mensaje_exito = '';
        if(Yii::$app->request->isPost)
        {
            $modelo->load(Yii::$app->request->post());
            
            if($modelo->clave != ''){
                $modelo->clave = sha1($modelo->clave);
            } else{
                $modelo->clave = $clave_anterior;
            }
            
            if($modelo->validate())
            {
                $modelo->save();
                
                
                $mensaje_exito = 'El Medico ha sido editado exitosamente.';
                Yii::$app->session['mensaje_flash'] = $mensaje_exito;
                
                Yii::$app->getResponse()->redirect([
                    'web/medicos'
                ])->send();
                return;
            }
            else
                $mensaje_error = 'Existe un error en los datos suministrados en el formulario.';
        }
        
        return $this->render('/web/medicos/agregar', [
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
            $modelo = Medicos::find()->where(['id' => $_POST['id']])->one();
            if($modelo != null)
            {
                $modelo->eliminado = true;
                $modelo->save();
                
                $json_respuesta = array(
                    'codigo' => '1',
                    'mensaje' => 'El medico se ha eliminado exitosamente...',
                );
            }
            else
            {
                $json_respuesta = array(
                    'codigo' => '0',
                    'mensaje' => 'Ha ocurrido un error al intentar eliminar el medico...',
                );
            }
        }
        
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $json_respuesta;
    }
}
