<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="street1", type="string")
 * @OA\Property(property="street2", type="string")
 * @OA\Property(property="street3", type="string")
 * @OA\Property(property="street4", type="string")
 * @OA\Property(property="compas", type="string")
 * @OA\Property(property="entrance_id", type="integer")
 */
class Tplfloor extends \common\models\Tplfloor {

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
	 * Проверка данных. Есть ли такой подъезд?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'Entrance not found');
		}
		if (isset($data['entrance_id'])) {
			if (!\common\models\Entrance::findIdentity($data['entrance_id'])) {
				throw new \yii\web\HttpException(404, 'Entrance not found');
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
