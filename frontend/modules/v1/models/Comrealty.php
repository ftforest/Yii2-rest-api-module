<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="name_search", type="string")
 * @OA\Property(property="address", type="string")
 * @OA\Property(property="area", type="string")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="imageplan", type="string")
 * @OA\Property(property="city_id", type="integer")
 * @OA\Property(property="coord", type="string")
 * @OA\Property(property="house_id", type="integer")
 * @OA\Property(property="description", type="string")
 */
class Comrealty extends \common\models\Comrealty {

	use \common\traits\ApiModelTrait;

	public function init() {
		parent::init();
		$this->_fld_photo = ['image','imageplan'];
	}

	public function extraFields() {
		return [
		];
	}

	/**
	 * Проверка данных. Есть ли такой город?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'City not found');
		}
		if (isset($data['city_id'])) {
			if (!\common\models\City::findIdentity($data['city_id'])) {
				throw new \yii\web\HttpException(404, 'City not found');
			}
		}
		return;
	}

	/**
	 * преобразуем и сохраняем изображение
	 * @param string $tmppath путь к временному файлу
	 */
	public function makeImage($tmppath, $ext, $field = '') {
		if(strtolower($ext)!='jpeg' && strtolower($ext)!='jpg' && strtolower($ext)!='png') {
			return;
		}
		if ($tmppath && file_exists($tmppath)) {
			$this->removePhoto($field);
			$path = $this->getFilePath();
			$filename = $this->getFileName($field, $ext);
			// SttImage::thumbnail($tmppath, $w, $h, ManipulatorInterface::THUMBNAIL_OUTBOUND)->save($path.$filename, ['quality' => 80]);
			copy($tmppath, $path.$filename);
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
		$field = $this->_fld_photo[0];
		$photo = $this->$field;
		$field2 = $this->_fld_photo[1];
		if($field2) {
			$photo_thumb = $this->$field2;
		} else {
			$photo_thumb = '';
		}
		$ret = parent::deleteAll(['id' => $id,]);
		if ($ret) {
			$path = $this->getFilePath();
			foreach (['gif', 'jpeg', 'png', 'jpg'] as $ext) {
				foreach ($this->_fld_photo as $fld) {
					$filename = $this->getFileName($fld, $ext);
					if(file_exists($path.$filename) && is_file($path.$filename)) {
						unlink($path.$filename);
					}
				}
			}
		}
		return $ret;
	}
	
	/**
	 * Получаем, какая должна быть ширина и высота изображения
	 * @param string $fld
	 * @return array
	 */
	public function getWidthHight($fld) {
		return [0,0];
	}
}
