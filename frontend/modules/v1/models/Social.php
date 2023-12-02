<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="type", type="string")
 * @OA\Property(property="complex_id", type="integer")
 * @OA\Property(property="coord", type="string")
 */
class Social extends \common\models\Social {

	use \common\traits\ApiModelTrait;
	
	public function extraFields() {
		return [
		];
	}

	/**
	 * Проверка данных. Есть ли такой комплекс?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'Complex not found');
		}
		if (isset($data['complex_id'])) {
			if (!\common\models\Complex::findIdentity($data['complex_id'])) {
				throw new \yii\web\HttpException(404, 'Complex not found');
			}
		}
		return;
	}
	
}
