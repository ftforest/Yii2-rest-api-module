<?php

namespace frontend\modules\v1\models;

use Yii;
use common\models\Media;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="email", type="string")
 * @OA\Property(property="txt", type="string")
 * @OA\Property(property="yesno", type="integer")
 * @OA\Property(property="date_create", type="integer")
 */
class Request extends \common\models\Request {

	use \common\traits\ApiModelTrait;

	public function fields() {
		$fields = parent::fields();
		$fields['images'] = function ($model) {
			return Media::findAll(['id_parent' => $model->id, 'parent' => 'request',]);
		};
		return $fields;
	}
	
	public function extraFields() {
		return [
		];
	}
}
