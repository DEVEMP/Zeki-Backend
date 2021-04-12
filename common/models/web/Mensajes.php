<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_mensajes".
 *
 * @property integer $id
 * @property string $mensaje_esp
 * @property string $mensaje_eng
 * @property string $mensaje_tur
 */
class Mensajes extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_mensajes';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mensaje_esp', 'mensaje_eng', 'mensaje_tur'], 'required'],
            [['mensaje_esp', 'mensaje_eng', 'mensaje_tur'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mensaje_esp' => 'Mensaje Esp',
            'mensaje_eng' => 'Mensaje Eng',
            'mensaje_tur' => 'Mensaje Tur',
        ];
    }

}
