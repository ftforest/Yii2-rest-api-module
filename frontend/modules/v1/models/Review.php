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
 * @OA\Property(property="email", type="string")
 * @OA\Property(property="date_create", type="integer")
 * @OA\Property(property="published", type="integer")
 */
class Review extends \common\models\Review {

	use \common\traits\ApiModelTrait;

	public function extraFields() {
		return [
		];
	}

}
