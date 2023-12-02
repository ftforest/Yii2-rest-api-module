<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id", "name",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="city_id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="phone", type="string")
 * @OA\Property(property="photo", type="string")
 * @OA\Property(property="email", type="string")
 * @OA\Property(property="position", type="string")
 */
class Worker extends \common\models\Worker {

	use \common\traits\ApiModelTrait;
	
	public function init() {
		parent::init();
		$this->_fld_photo = 'photo';
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
	 * Получаем, какая должна быть ширина и высота изображения
	 * @param string $fld
	 * @return array
	 */
	public function getWidthHight($fld) {
		if($fld == $this->_fld_photo) {
			return [410,290];
		}
		return [0,0];
	}
	
}
