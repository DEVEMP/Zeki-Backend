<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_estados_mensajes".
 *
 * @property integer $id
 * @property integer $estado
 * @property integer $mensaje
 *
 * @property Estados $estado0
 * @property Mensajes $mensaje0
 */
class EstadosMensajes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_estados_mensajes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['estado', 'mensaje'], 'required'],
            [['estado', 'mensaje'], 'integer'],
            [['estado'], 'exist', 'skipOnError' => true, 'targetClass' => Estados::className(), 'targetAttribute' => ['estado' => 'id']],
            [['mensaje'], 'exist', 'skipOnError' => true, 'targetClass' => Mensajes::className(), 'targetAttribute' => ['mensaje' => 'id']],
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
            'mensaje' => 'Mensaje',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEstado0()
    {
        return $this->hasOne(Estados::className(), ['id' => 'estado']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMensaje0()
    {
        return $this->hasOne(Mensajes::className(), ['id' => 'mensaje']);
    }
}
