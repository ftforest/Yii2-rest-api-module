<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="email", type="string")
 * @OA\Property(property="theme", type="string")
 * @OA\Property(property="quest", type="string")
 * @OA\Property(property="answer", type="string")
 * @OA\Property(property="house_id", type="integer")
 * @OA\Property(property="city_id", type="integer")
 * @OA\Property(property="date_create", type="integer")
 * @OA\Property(property="published", type="integer")
 * @OA\Property(property="emailsended", type="integer")
 */
class Quest extends \common\models\Quest {

	use \common\traits\ApiModelTrait;

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
	protected static function validData(&$data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'City not found');
		}
		if (isset($data['city_id'])) {
			if (!\common\models\City::findIdentity($data['city_id'])) {
				throw new \yii\web\HttpException(404, 'City not found');
			}
		}
		if(!isset($data['id']) && !isset($data['city_id'])) {
			// новый вопрос, а город не указан
			throw new \yii\web\HttpException(404, 'City not found');
		}
		if(!isset($data['id'])) {
			if(!isset($data['house_id']) || !$data['house_id']) {
				if(isset($data['theme']) && $data['theme']) {
					$house_id = static::getDb()->createCommand('select h.id from house as h left join complex as c on c.id=h.complex_id where theme_quest=:tq and c.city_id=:c', ['tq' => $data['theme'], 'c' => $data['city_id'],])->queryScalar();
					if($house_id) {
						$data['house_id'] = $house_id;
					}
				}
			}
		}
		return;
	}
	
	/**
	 * Отправка email при ответе
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes) {
		parent::afterSave($insert, $changedAttributes);
		if(!$insert && isset($changedAttributes['answer']) && $this->answer!=$changedAttributes['answer'] && !$this->emailsended) {
			$this->sendEmail();
		}
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
