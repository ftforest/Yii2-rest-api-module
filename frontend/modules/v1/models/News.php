<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="slug", type="string")
 * @OA\Property(property="text", type="string")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="date_create", type="integer")
 * @OA\Property(property="published", type="integer")
 */
class News extends \common\models\News {

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
		return [0,0];
	}
}
