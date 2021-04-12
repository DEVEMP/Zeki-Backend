<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_medicos".
 *
 * @property integer $id
 * @property string $codigo
 * @property string $nombre
 * @property string $apellido
 * @property string $especialidad
 * @property string $email
 * @property string $usuario
 * @property string $clave
 * @property string $fecha_registro
 * @property integer $eliminado
 *
 * @property Historiales[] $webHistoriales
 */
class Medicos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_medicos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['codigo', 'nombre', 'apellido', 'especialidad', 'email', 'usuario', 'clave'], 'required'],
            [['fecha_registro'], 'safe'],
            [['eliminado'], 'integer'],
            [['codigo'], 'string', 'max' => 60],
            [['nombre', 'apellido', 'email', 'usuario', 'clave'], 'string', 'max' => 100],
            [['especialidad'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'codigo' => 'Cedula Profesional',
            'nombre' => 'Nombre',
            'apellido' => 'Apellido',
            'especialidad' => 'Especialidad',
            'email' => 'Email',
            'usuario' => 'Usuario',
            'clave' => 'Clave',
            'fecha_registro' => 'Fecha Registro',
            'eliminado' => 'Eliminado',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistoriales()
    {
        return $this->hasMany(Historiales::className(), ['medico' => 'id']);
    }
}
