<?php

namespace frontend\modules\v1\models;

use Yii;
use common\extensions\SttImage;
use Imagine\Image\ManipulatorInterface;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="address", type="string")
 * @OA\Property(property="category", type="string")
 * @OA\Property(property="rooms", type="integer")
 * @OA\Property(property="city_id", type="integer")
 * @OA\Property(property="area", type="string")
 * @OA\Property(property="coord", type="string")
 * @OA\Property(property="price", type="string")
 * @OA\Property(property="description", type="string")
 * @OA\Property(property="total_area", type="string")
 * @OA\Property(property="living_area", type="string")
 * @OA\Property(property="kitchen_area", type="string")
 * @OA\Property(property="balcony_area", type="string")
 * @OA\Property(property="plot_area", type="string")
 * @OA\Property(property="floors", type="string")
 * @OA\Property(property="floor", type="string")
 * @OA\Property(property="entrance", type="string")
 * @OA\Property(property="balcony", type="string")
 * @OA\Property(property="cadastr", type="string")
 * @OA\Property(property="cadastr_link", type="string")
 * @OA\Property(property="published", type="integer")
 */
class Resale extends \common\models\Resale {
	
	use \common\traits\ApiModelTrait;

	public static $images = null;
	
	/**
	 * Добавляем каждому объекту первое изображение
	 * @param array $items массив объектов
	 * @return array
	 */
	public static function addImage($items) {
		$ret = [];
		if(static::$images===null) {
			$media = Media::find()->where(['parent' => 'resale',])->asArray()->orderBy('id desc')->all();
			static::$images = [];
			if($media) {
				foreach ($media as $m) {
					static::$images[$m['id_parent']] = $m['image'];
				}
			}
		}
		if($items) {
			foreach ($items as $item) {
				$item['image'] = isset(static::$images[$item['id']])?static::$images[$item['id']]:NULL;
				$ret[] = $item;
			}
		}
		return $ret;
	}

	public function extraFields() {
		return [
		];
	}

	/**
	 * Создание новой записи
	 * @param array $medias
	 * @param int $category
	 * @return type
	 */
	public static function createcat($medias = array(), $category = 1) {
		$ret = static::create($medias);
		if(isset($ret['id'])) {
			$model = static::findIdentity($ret['id']);
			if($model) {
				$model->category = $category;
				$model->save(FALSE, ['category']);
			}
		}
		return $ret;
	}
	
	/**
	 * Проверка данных. Есть ли такой город?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'City not found');
		}
		if (isset($data['city_id'])) {
			if (!\common\models\City::findIdentity($data['city_id'])) {
				throw new \yii\web\HttpException(404, 'City not found');
			}
		}
		return;
	}
}
