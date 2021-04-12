<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_acciones".
 *
 * @property integer $id
 * @property string $condicion
 * @property string $valor_esp
 * @property string $valor_eng
 * @property string $valor_tur
 * @property integer $origen
 * @property integer $destino
 *
 * @property Estados $origen0
 * @property Estados $destino0
 */
class Acciones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_acciones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['condicion', 'valor_esp', 'valor_eng', 'valor_tur', 'origen', 'destino'], 'required'],
            [['origen', 'destino'], 'integer'],
            [['condicion', 'valor_esp', 'valor_eng', 'valor_tur'], 'string', 'max' => 150],
            [['origen'], 'exist', 'skipOnError' => true, 'targetClass' => Estados::className(), 'targetAttribute' => ['origen' => 'id']],
            [['destino'], 'exist', 'skipOnError' => true, 'targetClass' => Estados::className(), 'targetAttribute' => ['destino' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'condicion' => 'Condicion',
            'valor_esp' => 'Valor Esp',
        	'valor_eng' => 'Valor Eng',
        	'valor_tur' => 'Valor Tur',
            'origen' => 'Origen',
            'destino' => 'Destino',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrigen0()
    {
        return $this->hasOne(Estados::className(), ['id' => 'origen']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDestino0()
    {
        return $this->hasOne(Estados::className(), ['id' => 'destino']);
    }
}
