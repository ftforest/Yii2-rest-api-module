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
 * @OA\Property(property="office_id", type="integer")
 * @OA\Property(property="date_create", type="integer")
 */
class Requestoffice extends \common\models\Requestoffice {

	use \common\traits\ApiModelTrait;

	public function fields() {
		$fields = parent::fields();
		$fields['city'] = function ($model) {
			return $model->office->comrealty->city->name;
		};
		$fields['comrealty'] = function ($model) {
			return $model->office->comrealty->name;
		};
		$fields['office'] = function ($model) {
			return $model->office->name;
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
			throw new \yii\web\HttpException(404, 'Office not found');
		}
		if(!isset($data['id']) && !isset($data['office_id'])) {
			// новая запись, а вторичка не указана
			throw new \yii\web\HttpException(404, 'Office not found');
		}
		if (isset($data['office_id'])) {
			if (!\common\models\Office::findIdentity($data['office_id'])) {
				throw new \yii\web\HttpException(404, 'Office not found');
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
				['field_id' => 256505, 'values' => [['value' => (string)$this->office->comrealty->address,]]], // адрес
				['field_id' => 256507, 'values' => [['value' => (string)$this->office->square,]]], // площадь
				['field_id' => 575864, 'values' => [['value' => (string)$this->office->price,]]], // цена
			];
			$lead_id = $amo->addLeads(Yii::$app->params['AmoCRM']['pipeline_id'], 'Сайт '.$this->type.' '.date('d.m.Y'),$custom);
			if($lead_id) {
				// добавляем примечание
				$text = 'Тип заявки: '. $this->type."\n";
				$text .= 'Имя: '. $this->name."\n";
				$text .= 'Телефон: '. $this->phone."\n";
				$text .= 'Город: '. $this->office->comrealty->city->name."\n";
				$text .= 'Коммерческая недвижимость: '. $this->office->comrealty->name."\n";
				$text .= 'Офис: '. $this->office->name."\n";
				$text .= 'Дата и время: '. date('d.m.Y H:i') . "\n";
				$amo->addLeadsNotes($lead_id,$text);
			}
		}
	}

}
