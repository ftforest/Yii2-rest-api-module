<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="slug", type="string")
 * @OA\Property(property="text", type="string")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="date_begin", type="integer")
 * @OA\Property(property="date_end", type="integer")
 * @OA\Property(property="order", type="integer")
 * @OA\Property(property="published", type="integer")
 */
class Promo extends \common\models\Promo {

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
	 * Получаем, какая должна быть ширина и высота изображения
	 * @param string $fld
	 * @return array
	 */
	public function getWidthHight($fld) {
		if($fld == $this->_fld_photo) {
			return [1200,609];
		}
		return [0,0];
	}
}
