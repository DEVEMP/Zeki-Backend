<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "usuarios".
 *
 * @property integer $id
 * @property string $usuario
 * @property string $nombre
 * @property string $apellido
 * @property string $email
 * @property string $clave
 * @property string $tipo_usuario
 * @property string $fecha_registro
 */
class Usuarios extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'usuarios';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['usuario', 'email', 'clave', 'tipo_usuario'], 'required'],
            [['fecha_registro'], 'safe'],
            [['usuario', 'clave'], 'string', 'max' => 60],
            [['email'], 'string', 'max' => 120],
        	[['email'], 'email'],
            [['tipo_usuario'], 'string', 'max' => 45],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'usuario' => 'Usuario',
            'email' => 'Email',
            'clave' => 'Clave',
            'tipo_usuario' => 'Tipo Usuario',
            'fecha_registro' => 'Fecha Registro',
        ];
    }
}
