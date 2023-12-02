<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="integer")
 * @OA\Property(property="tplapart_id", type="integer")
 * @OA\Property(property="square", type="string")
 * @OA\Property(property="floor", type="integer")
 * @OA\Property(property="rooms", type="string")
 * @OA\Property(property="price", type="string")
 * @OA\Property(property="status", type="string")
 */
class Apart extends \common\models\Apart {

	use \common\traits\ApiModelTrait;
	
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
			throw new \yii\web\HttpException(404, 'Tplapart not found');
		}
		if (isset($data['tplapart_id'])) {
			if (!\common\models\Tplapart::findIdentity($data['tplapart_id'])) {
				throw new \yii\web\HttpException(404, 'Tplapart not found');
			}
		}
		return;
	}
	
}
