<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notificaciones_emails".
 *
 * @property integer $id
 * @property string $para
 * @property string $titulo
 * @property string $variables
 * @property integer $enviado
 * @property string $fecha_creacion
 * @property integer $notificacion
 *
 * @property Notificaciones $notificacion0
 */
class NotificacionesEmails extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'notificaciones_emails';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['para', 'titulo', 'variables', 'enviado', 'notificacion'], 'required'],
            [['variables'], 'string'],
            [['enviado', 'notificacion'], 'integer'],
            [['fecha_creacion'], 'safe'],
            [['para'], 'string', 'max' => 100],
            [['titulo'], 'string', 'max' => 150],
            [['notificacion'], 'exist', 'skipOnError' => true, 'targetClass' => Notificaciones::className(), 'targetAttribute' => ['notificacion' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'para' => 'Para',
            'titulo' => 'Titulo',
            'variables' => 'Variables',
            'enviado' => 'Enviado',
            'fecha_creacion' => 'Fecha Creacion',
            'notificacion' => 'Notificacion',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNotificacion0()
    {
        return $this->hasOne(Notificaciones::className(), ['id' => 'notificacion']);
    }
}
