<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * 		@OA\Property(property="id", type="integer")
 * 		@OA\Property(property="name", type="string")
 * 		@OA\Property(property="complex_id", type="integer")
 * 		@OA\Property(property="plan", type="string")
 * 		@OA\Property(property="status", type="string")
 * 		@OA\Property(property="metka", type="string")
 * 		@OA\Property(property="theme_quest", type="string")
 * 		@OA\Property(property="promo", type="integer")
 * 		@OA\Property(property="counter", type="integer")
 * 		@OA\Property(property="counter_text", type="string")
 * 		@OA\Property(property="counter_proc0", type="integer")
 * 		@OA\Property(property="counter_date_begin", type="string")
 * 		@OA\Property(property="counter_date_end", type="string")
 * 		@OA\Property(property="showprice", type="integer")
 * 		@OA\Property(property="tradein", type="integer")
 * 		@OA\Property(property="description", type="string")
 * 		@OA\Property(property="street", type="string")
 * 		@OA\Property(property="video", type="string")
 * 		@OA\Property(property="virtualtour", type="string")
 * 		@OA\Property(property="instruction", type="string")
 * 		@OA\Property(property="imageplan", type="string")
 * 		@OA\Property(property="geoposition", type="string")
 */
class House extends \common\models\House {

	use \common\traits\ApiModelTrait;

	public function init() {
		parent::init();
		$this->_fld_photo = 'imageplan';
	}

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

	/**
	 * Получаем, какая должна быть ширина и высота изображения
	 * @param string $fld
	 * @return array
	 */
	public function getWidthHight($fld) {
		if($fld == $this->_fld_photo) {
			return [900,900];
		}
		return [0,0];
	}
}
