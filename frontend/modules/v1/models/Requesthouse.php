<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="type", type="string")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="phone", type="string")
 * @OA\Property(property="house_id", type="integer")
 * @OA\Property(property="date_create", type="integer")
 */
class Requesthouse extends \common\models\Requesthouse {

	use \common\traits\ApiModelTrait;

	public function fields() {
		$fields = parent::fields();
		$fields['complex'] = function ($model) {
			return $model->house->complex->name;
		};
		$fields['city'] = function ($model) {
			return $model->house->complex->city->name;
		};
		$fields['house'] = function ($model) {
			return $model->house->name;
		};
		return $fields;
	}

	public function extraFields() {
		return [
		];
	}

	/**
	 * Проверка данных. Есть ли такая квартира?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'House not found');
		}
		if(!isset($data['id']) && !isset($data['house_id'])) {
			// новая запись, а квартира не указана
			throw new \yii\web\HttpException(404, 'House not found');
		}
		if (isset($data['house_id'])) {
			if (!\common\models\House::findIdentity($data['house_id'])) {
				throw new \yii\web\HttpException(404, 'House not found');
			}
		}
		return;
	}

	/**
	 * Отправка заявки в CRM
	 * @param bool $insert
	 * @param array $changedAttributes
	 */
	public function afterSave($insert, $changedAttributes) {
		parent::afterSave($insert, $changedAttributes);
		if($insert) {
			$amo = new \common\extensions\amocrm();
			// добавляем сделку
			$custom = [
				['field_id' => 255843, 'values' => [['value' => (string)$this->house->complex->name,]]], // жк
				['field_id' => 255845, 'values' => [['value' => (string)$this->house->name,]]], // дом
			];
			$lead_id = $amo->addLeads(Yii::$app->params['AmoCRM']['pipeline_id'], 'Сайт '.$this->type.' '.date('d.m.Y'),$custom);
			if($lead_id) {
				// добавляем примечание
				$text = 'Тип заявки: '. $this->type."\n";
				$text .= 'Имя: '. $this->name."\n";
				$text .= 'Телефон: '. $this->phone."\n";
				$text .= 'Город: '. $this->house->complex->city->name."\n";
				$text .= 'ЖК: '. $this->house->complex->name."\n";
				$text .= 'Дом: '. $this->house->name."\n";
				$text .= 'Дата и время: '. date('d.m.Y H:i') . "\n";
				$amo->addLeadsNotes($lead_id,$text);
			}
		}
	}

}
