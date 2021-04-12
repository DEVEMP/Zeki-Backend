<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_traducciones_turco".
 *
 * @property integer $id
 * @property string $frase
 * @property string $traduccion
 * @property integer $palabras
 */
class TraduccionesTurco extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_traducciones_turco';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['frase', 'traduccion', 'palabras'], 'required'],
            [['frase', 'traduccion'], 'string'],
            [['palabras'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'frase' => 'Frase',
            'traduccion' => 'Traduccion',
            'palabras' => 'Palabras',
        ];
    }
}
