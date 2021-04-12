<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "parametros".
 *
 * @property integer $id
 * @property integer $orden
 * @property string $parametro
 * @property string $valor
 */
class Parametros extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'parametros';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['orden', 'parametro', 'valor'], 'required'],
            [['orden'], 'integer'],
            [['valor'], 'string'],
            [['parametro'], 'string', 'max' => 150],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'orden' => 'Orden',
            'parametro' => 'Parametro',
            'valor' => 'Valor',
        ];
    }
}
