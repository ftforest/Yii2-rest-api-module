<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="parkinglvl_id", type="integer")
 * @OA\Property(property="plan", type="string")
 * @OA\Property(property="square", type="string")
 * @OA\Property(property="price", type="string")
 * @OA\Property(property="status", type="string")
 * @OA\Property(property="id_1s", type="string")
 */
class Parkingplace extends \common\models\Parkingplace {

	use \common\traits\ApiModelTrait;

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
			throw new \yii\web\HttpException(404, 'Parkinglvl not found');
		}
		if (isset($data['parkinglvl_id'])) {
			if (!\common\models\Parkinglvl::findIdentity($data['parkinglvl_id'])) {
				throw new \yii\web\HttpException(404, 'Parkinglvl not found');
			}
		}
		return;
	}

}
