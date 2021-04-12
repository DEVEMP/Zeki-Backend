<?php

namespace common\forms;

use yii\base\Model;
use yii\web\UploadedFile;

class BannersForm extends Model
{
	public $url;
	public $descripcion;
	public $imagen;
	
	public function rules()
	{
		return [
			[['url', 'descripcion'], 'required'],
			[['url'], 'string', 'max' => 50],
			[['imagen'], 'file', 'extensions' => 'png, jpg'],
		];
	}
	
	public function upload()
	{
		if ($this->validate()) {
			$imagen_obj = UploadedFile::getInstance($this, 'imagen');
			
			$url = __DIR__ . '/../../www/media/banners/';			
			if(!file_exists($url)){
				mkdir($url, 0777, true);
			}
			
			$imagen_obj->saveAs($url . $imagen_obj->baseName . '.' . $imagen_obj->extension);

			return true;
		} else {
			return false;
		}
	}
}