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
 * @OA\Property(property="apart_id", type="integer")
 * @OA\Property(property="date_create", type="integer")
 */
class Requestapart extends \common\models\Requestapart {

	use \common\traits\ApiModelTrait;

	public function fields() {
		$fields = parent::fields();
		$fields['complex'] = function ($model) {
			return $model->apart->tplapart->tplfloor->entrance->house->complex->name;
		};
		$fields['city'] = function ($model) {
			return $model->apart->tplapart->tplfloor->entrance->house->complex->city->name;
		};
		$fields['house'] = function ($model) {
			return $model->apart->tplapart->tplfloor->entrance->house->name;
		};
		$fields['entrance'] = function ($model) {
			return $model->apart->tplapart->tplfloor->entrance->name;
		};
		$fields['floor'] = function ($model) {
			return $model->apart->floor;
		};
		$fields['apart_num'] = function ($model) {
			return $model->apart->name;
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
			throw new \yii\web\HttpException(404, 'Apart not found');
		}
		if(!isset($data['id']) && !isset($data['apart_id'])) {
			// новая запись, а квартира не указана
			throw new \yii\web\HttpException(404, 'Apart not found');
		}
		if (isset($data['apart_id'])) {
			if (!\common\models\Apart::findIdentity($data['apart_id'])) {
				throw new \yii\web\HttpException(404, 'Apart not found');
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
				['field_id' => 255843, 'values' => [['value' => (string)$this->apart->tplapart->tplfloor->entrance->house->complex->name,]]], // жк
				['field_id' => 255845, 'values' => [['value' => (string)$this->apart->tplapart->tplfloor->entrance->house->name,]]], // дом
				['field_id' => 255847, 'values' => [['value' => (string)$this->apart->tplapart->tplfloor->entrance->name,]]], // подъезд
				['field_id' => 255849, 'values' => [['value' => (string)$this->apart->floor,]]], // этаж
				['field_id' => 255851, 'values' => [['value' => (string)$this->apart->name,]]], // кв
			];
			$lead_id = $amo->addLeads(Yii::$app->params['AmoCRM']['pipeline_id'], 'Сайт '.$this->type.' '.date('d.m.Y'),$custom);
			if($lead_id) {
				// добавляем примечание
				$text = 'Тип заявки: '. $this->type."\n";
				$text .= 'Имя: '. $this->name."\n";
				$text .= 'Телефон: '. $this->phone."\n";
				$text .= 'Город: '. $this->apart->tplapart->tplfloor->entrance->house->complex->city->name."\n";
				$text .= 'ЖК: '. $this->apart->tplapart->tplfloor->entrance->house->complex->name."\n";
				$text .= 'Дом: '. $this->apart->tplapart->tplfloor->entrance->house->name."\n";
				$text .= 'Подъезд: '. $this->apart->tplapart->tplfloor->entrance->name."\n";
				$text .= 'Этаж: '. $this->apart->floor."\n";
				$text .= 'Кв: '. $this->apart->name."\n";
				$text .= 'Дата и время: '. date('d.m.Y H:i') . "\n";
				$amo->addLeadsNotes($lead_id,$text);
			}
		}
	}

}
