<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notificaciones".
 *
 * @property integer $id
 * @property string $identificador
 * @property string $contenido
 *
 * @property NotificacionesEmails[] $notificacionesEmails
 */
class Notificaciones extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notificaciones';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['identificador', 'contenido'], 'required'],
            [['contenido'], 'string'],
            [['identificador'], 'string', 'max' => 150],
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
            'contenido' => 'Contenido',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotificacionesEmails()
    {
        return $this->hasMany(NotificacionesEmails::className(), ['notificacion' => 'id']);
    }
}
