<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="comrealty_id", type="integer")
 * @OA\Property(property="plan", type="string")
 * @OA\Property(property="square", type="string")
 * @OA\Property(property="price", type="string")
 * @OA\Property(property="status", type="string")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="id_1s", type="string")
 */
class Office extends \common\models\Office {

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
			throw new \yii\web\HttpException(404, 'Comrealty not found');
		}
		if (isset($data['comrealty_id'])) {
			if (!\common\models\Comrealty::findIdentity($data['comrealty_id'])) {
				throw new \yii\web\HttpException(404, 'Comrealty not found');
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
