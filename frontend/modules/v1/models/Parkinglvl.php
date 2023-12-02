<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="parking_id", type="integer")
 */
class Parkinglvl extends \common\models\Parkinglvl {

	use \common\traits\ApiModelTrait;

	public function init() {
		parent::init();
		$this->_fld_photo = 'image';
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
			throw new \yii\web\HttpException(404, 'Parking not found');
		}
		if (isset($data['parking_id'])) {
			if (!\common\models\Parking::findIdentity($data['parking_id'])) {
				throw new \yii\web\HttpException(404, 'Parking not found');
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
		return [0,0];
	}
}
