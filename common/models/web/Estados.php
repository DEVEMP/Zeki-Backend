<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_estados".
 *
 * @property integer $id
 * @property string $estado
 * @property string $tipo_estado
 * @property string $palabras_claves_esp
 * @property string $palabras_claves_eng
 * @property string $palabras_claves_tur
 *
 * @property Acciones[] $webAcciones
 * @property Acciones[] $webAcciones0
 * @property EstadosMensajes[] $webEstadosMensajes
 */
class Estados extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_estados';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['estado', 'tipo_estado'], 'required'],
            [['palabras_claves_esp', 'palabras_claves_eng', 'palabras_claves_tur'], 'string'],
            [['estado'], 'string', 'max' => 150],
            [['tipo_estado'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'estado' => 'Estado',
            'tipo_estado' => 'Tipo Estado',
            'palabras_claves_esp' => 'Palabras Claves Esp',
            'palabras_claves_eng' => 'Palabras Claves Eng',
            'palabras_claves_tur' => 'Palabras Claves Tur',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcciones()
    {
        return $this->hasMany(Acciones::className(), ['origen' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAcciones0()
    {
        return $this->hasMany(Acciones::className(), ['destino' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEstadosMensajes()
    {
        return $this->hasMany(EstadosMensajes::className(), ['estado' => 'id']);
    }
    
    
    public function obtenerMensaje($idioma)
    {
        $estadosmensajes_disponibles = EstadosMensajes::find()
        ->andWhere(['estado' => $this->id])
        ->all();
        
        if(count($estadosmensajes_disponibles) > 0)
        {
            $array_estadosmensajes = array();
            foreach($estadosmensajes_disponibles as $estadomensaje)
            {
                $array_estadosmensajes[] = $estadomensaje->id;
            }
            
            // PARA COLOCARLO ALEATORIO
            shuffle($array_estadosmensajes);
            
            $estadomensaje_seleccionado = EstadosMensajes::find()
            ->andWhere(['id' => $array_estadosmensajes[0]])
            ->one();
            
            if($idioma == 'ESP'){
                return $estadomensaje_seleccionado->mensaje0->mensaje_esp;
            }
            
            if($idioma == 'ENG'){
                return $estadomensaje_seleccionado->mensaje0->mensaje_eng;
            }
            
            if($idioma == 'TUR'){
                return $estadomensaje_seleccionado->mensaje0->mensaje_tur;
            }
        }
        else
        {
            if($idioma == 'ESP'){
                return 'No encuentro nada interesante en mi colección sobre este tema.';
            }
            
            if($idioma == 'ENG'){
                return 'I do not find anything interesting in my collection on this subject.';
            }
            
            if($idioma == 'TUR'){
                return 'Koleksiyonumda bu konuda ilginç bir şey bulamıyorum.';
            }
        }
    }
}
