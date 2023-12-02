<?php

namespace frontend\modules\v1\models;

use Yii;
use common\extensions\SttImage;
use Imagine\Image\ManipulatorInterface;

/**
 * @OA\Schema(required={"id", "name",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="published", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="phone", type="string")
 * @OA\Property(property="qr", type="string")
 * @OA\Property(property="video1", type="string")
 * @OA\Property(property="video2", type="string")
 * @OA\Property(property="background1", type="string")
 * @OA\Property(property="background2", type="string")
 * @OA\Property(property="iswhite", type="integer")
 * @OA\Property(property="isnight", type="integer")
 * @OA\Property(property="map", type="string")
 * @OA\Property(property="info", type="string")
 */
class City extends \common\models\City {
	use \common\traits\ApiModelTrait;

	public function init() {
		parent::init();
		$this->_fld_photo = ['qr','video1','video2','background1','background2'];
	}
	
	public function extraFields() {
		return [
		];
	}

	/**
	 * преобразуем и сохраняем изображение
	 * @param string $tmppath путь к временному файлу
	 */
	public function makeImage($tmppath, $ext, $field = '') {
		if(!$field) {
			return;
		}
		if($field!='video1' && $field!='video2') {
			if(strtolower($ext)!='jpeg' && strtolower($ext)!='jpg' && strtolower($ext)!='png') {
				return;
			}
		}
		if ($tmppath && file_exists($tmppath)) {
			$w = 1920;
			$h = 1080;
			$this->removePhoto($field);
			$path = $this->getFilePath();
			$filename = $this->getFileName($field, $ext);
			if($field=='background1' || $field=='background2') {
				SttImage::thumbnail($tmppath, $w, $h, ManipulatorInterface::THUMBNAIL_OUTBOUND)->save($path.$filename, ['quality' => 80]);
			} else {
				copy($tmppath, $path.$filename);
			}
			$this->$field = $filename;
			$attrs = [$field];
			unlink($tmppath);
			parent::save(false, $attrs);
		}
	}
	
	/**
	 * Удаляем запись и файл изображения
	 */
	public function delete() {
		$id = $this->id;
		$files = [];
		foreach ($this->_fld_photo as $f) {
			if($this->$f) {
				$files[] = $this->$f;
			}
		}
		$ret = parent::deleteAll(['id' => $id,]);
		if ($ret) {
			$path = $this->getFilePath();
			foreach ($files as $filename) {
				if(file_exists($path.$filename) && is_file($path.$filename)) {
					unlink($path.$filename);
				}
			}
		}
		return $ret;
	}
	
}
