<?php

namespace frontend\modules\v1\models;

use Yii;
use yii\web\HttpException;

/**
 * @OA\Schema(required={"id", "username",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="city", type="string")
 * @OA\Property(property="title", type="string")
 * @OA\Property(property="value", type="string")
 * @OA\Property(property="type", type="string")
 */
class Settings extends \common\models\Settings {

	use \common\traits\ApiModelTrait;

	public function extraFields() {
		return [
		];
	}
}
