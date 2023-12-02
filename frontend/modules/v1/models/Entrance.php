<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="integer")
 * @OA\Property(property="house_id", type="integer")
 * @OA\Property(property="floors", type="integer")
 * @OA\Property(property="plan", type="string")
 */
class Entrance extends \common\models\Entrance {

	use \common\traits\ApiModelTrait;

	public function extraFields() {
		return [
		];
	}

	/**
	 * Проверка данных. Есть ли такой дом?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'House not found');
		}
		if (isset($data['house_id'])) {
			if (!\common\models\House::findIdentity($data['house_id'])) {
				throw new \yii\web\HttpException(404, 'House not found');
			}
		}
		return;
	}
	
}
