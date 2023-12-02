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
 * @OA\Property(property="parkingplace_id", type="integer")
 * @OA\Property(property="date_create", type="integer")
 */
class Requestparking extends \common\models\Requestparking {

	use \common\traits\ApiModelTrait;

	public function fields() {
		$fields = parent::fields();
		$fields['city'] = function ($model) {
			return $model->parkingplace->parkinglvl->parking->city->name;
		};
		$fields['parking'] = function ($model) {
			return $model->parkingplace->parkinglvl->parking->name;
		};
		$fields['parkinglvl'] = function ($model) {
			return $model->parkingplace->parkinglvl->name;
		};
		$fields['parkingplace'] = function ($model) {
			return $model->parkingplace->name;
		};
		return $fields;
	}

	public function extraFields() {
		return [
		];
	}

	/**
	 * Проверка данных. Есть ли такое парковочное место?
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData($data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'Parking place not found');
		}
		if(!isset($data['id']) && !isset($data['parkingplace_id'])) {
			// новая запись, а парковка не указана
			throw new \yii\web\HttpException(404, 'Parking place not found');
		}
		if (isset($data['parkingplace_id'])) {
			if (!\common\models\Parkingplace::findIdentity($data['parkingplace_id'])) {
				throw new \yii\web\HttpException(404, 'Parking place not found');
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
			];
			$lead_id = $amo->addLeads(Yii::$app->params['AmoCRM']['pipeline_id'], 'Сайт '.$this->type.' '.date('d.m.Y'),$custom);
			if($lead_id) {
				// добавляем примечание
				$text = 'Тип заявки: '. $this->type."\n";
				$text .= 'Имя: '. $this->name."\n";
				$text .= 'Телефон: '. $this->phone."\n";
				$text .= 'Город: '. $this->parkingplace->parkinglvl->parking->city->name."\n";
				$text .= 'Паркинг: '. $this->parkingplace->parkinglvl->parking->name."\n";
				$text .= 'Уровень: '. $this->parkingplace->parkinglvl->name."\n";
				$text .= 'Место: '. $this->parkingplace->name."\n";
				$text .= 'Дата и время: '. date('d.m.Y H:i') . "\n";
				$amo->addLeadsNotes($lead_id,$text);
			}
		}
	}
}
