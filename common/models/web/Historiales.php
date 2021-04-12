<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_historiales".
 *
 * @property integer $id
 * @property string $identificador
 * @property string $tipo_mensaje
 * @property string $mensaje
 * @property string $fecha_mensaje
 * @property integer $medico
 *
 * @property Medicos $medico0
 */
class Historiales extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_historiales';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['identificador', 'tipo_mensaje', 'mensaje', 'medico'], 'required'],
            [['mensaje'], 'string'],
            [['fecha_mensaje'], 'safe'],
            [['medico'], 'integer'],
            [['identificador'], 'string', 'max' => 100],
            [['tipo_mensaje'], 'string', 'max' => 45],
            [['medico'], 'exist', 'skipOnError' => true, 'targetClass' => Medicos::className(), 'targetAttribute' => ['medico' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'identificador' => 'Identificador',
            'tipo_mensaje' => 'Tipo Mensaje',
            'mensaje' => 'Mensaje',
            'fecha_mensaje' => 'Fecha Mensaje',
            'medico' => 'Medico',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMedico0()
    {
        return $this->hasOne(Medicos::className(), ['id' => 'medico']);
    }
}
