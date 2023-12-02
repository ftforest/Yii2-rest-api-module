<?php

namespace frontend\modules\v1\models;

use Yii;
use common\extensions\SttImage;
use Imagine\Image\ManipulatorInterface;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="tplfloor_id", type="integer")
 * @OA\Property(property="plan", type="string")
 * @OA\Property(property="cnt", type="integer")
 * @OA\Property(property="image2d", type="string")
 * @OA\Property(property="image3d", type="string")
 */
class Tplapart extends \common\models\Tplapart {

	use \common\traits\ApiModelTrait;
	
	public function init() {
		parent::init();
		$this->_fld_photo = ['image2d','image3d'];
	}

	public function extraFields() {
		return [
		];
	}

	/**
	 * Проверка данных. Есть ли такой шаблон?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'Tplfloor not found');
		}
		if (isset($data['tplfloor_id'])) {
			if (!\common\models\Tplfloor::findIdentity($data['tplfloor_id'])) {
				throw new \yii\web\HttpException(404, 'Tplfloor not found');
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
			$w = 900;
			$h = 900;
			$this->removePhoto($field);
			$path = $this->getFilePath();
			$filename = $this->getFileName($field, $ext);
			SttImage::thumbnail($tmppath, $w, $h, ManipulatorInterface::THUMBNAIL_OUTBOUND)->save($path.$filename, ['quality' => 80]);
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
			foreach ([$photo_thumb, $photo, $this->getFileName(''), $this->getFileName($field2), $this->getFileName('', 'png'), $this->getFileName($field2, 'png'), $this->getFileName('', 'gif'), $this->getFileName($field2, 'gif')] as $filename) {
				if(file_exists($path.$filename) && is_file($path.$filename)) {
					unlink($path.$filename);
				}
			}
		}
		return $ret;
	}
	
}
