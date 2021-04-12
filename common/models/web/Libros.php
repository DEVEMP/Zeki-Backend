<?php

namespace common\models\web;

use Yii;

/**
 * This is the model class for table "web_libros".
 *
 * @property integer $id
 * @property string $title
 * @property string $author
 * @property string $keywords
 * @property string $magazine
 * @property string $publication_date
 * @property string $abstract
 * @property string $content
 * @property string $bibliography
 * @property string $file
 */
class Libros extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'web_libros';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'required'],
            [['title', 'author', 'keywords', 'magazine', 'abstract', 'content', 'bibliography', 'file'], 'string'],
            [['publication_date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'author' => 'Author',
            'keywords' => 'Keywords',
            'magazine' => 'Magazine',
            'publication_date' => 'Publication Date',
            'abstract' => 'Abstract',
            'content' => 'Content',
            'bibliography' => 'Bibliography',
            'file' => 'File',
        ];
    }
}
