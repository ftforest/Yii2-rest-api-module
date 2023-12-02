<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="image", type="string")
 * @OA\Property(property="parent", type="string")
 * @OA\Property(property="type", type="string")
 * @OA\Property(property="id_parent", type="integer")
 * @OA\Property(property="published", type="integer")
 * @OA\Property(property="order", type="integer")
 */
class Media extends \common\models\Media {

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
	 * Проверка данных. Есть ли родительская запись?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		$classes = ['news', 'house', 'complex', 'resale', 'comrealty', 'parking', 'quest', 'request'];
		if (isset($data['id_parent']) && (!isset($data['parent']))) {
			$data['parent'] = $classes[0];
		}
		if(isset($data['parent'])) {
			if(!in_array($data['parent'], $classes)) {
				throw new \yii\web\HttpException(404, 'Parent not found');
			}
		}
		if (isset($data['id_parent'])) {
			$class = '\common\models'.'\\'.ucfirst($data['parent']);
			if (!$class::findIdentity($data['id_parent'])) {
				throw new \yii\web\HttpException(404, 'News not found');
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
		if($this->parent=='comrealty') {
			return [0,0];
		}
		if($this->parent=='news') {
			return [1200,581];
		}
		if($this->parent=='parking') {
			return [0,0];
		}
		if($this->parent=='quest') {
			return [980,980];
		}
		if($this->parent=='house') {
			if($this->type=='gallery') {
				return [0,0];
			}
			if($this->type=='view') {
				return [0,0];
			}
		}
		return [0,0];
	}
}
