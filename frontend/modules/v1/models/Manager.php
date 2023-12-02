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
 * @OA\Property(property="category", type="string")
 * @OA\Property(property="email", type="string")
 */
class Manager extends \common\models\Manager {

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
			throw new \yii\web\HttpException(404, 'City not found');
		}
		if (isset($data['city_id'])) {
			if (!\common\models\City::findIdentity($data['city_id'])) {
				throw new \yii\web\HttpException(404, 'City not found');
			}
		}
		return;
	}

}
