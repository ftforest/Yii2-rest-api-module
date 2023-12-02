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
 * @OA\Property(property="resale_id", type="integer")
 * @OA\Property(property="date_create", type="integer")
 */
class Requestresale extends \common\models\Requestresale {

	use \common\traits\ApiModelTrait;

	public function fields() {
		$fields = parent::fields();
		$fields['city'] = function ($model) {
			return $model->resale->city->name;
		};
		$fields['category'] = function ($model) {
			$categories = \common\models\Resale::getCategories();
			$c = $model->resale->category;
			return isset($categories[$c])?$categories[$c]:'';
		};
		$fields['area'] = function ($model) {
			return $model->resale->area;
		};
		$fields['total_area'] = function ($model) {
			return $model->resale->total_area;
		};
		$fields['price'] = function ($model) {
			return $model->resale->price;
		};
		$fields['floor'] = function ($model) {
			return $model->resale->floor;
		};
		$fields['address'] = function ($model) {
			return $model->resale->address;
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
			throw new \yii\web\HttpException(404, 'Resale not found');
		}
		if(!isset($data['id']) && !isset($data['resale_id'])) {
			// новая запись, а вторичка не указана
			throw new \yii\web\HttpException(404, 'Resale not found');
		}
		if (isset($data['resale_id'])) {
			if (!\common\models\Resale::findIdentity($data['resale_id'])) {
				throw new \yii\web\HttpException(404, 'Resale not found');
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
			$categories = \common\models\Resale::getCategories();
			$c = $this->resale->category;
			$category = isset($categories[$c])?$categories[$c]:'';
			$amo = new \common\extensions\amocrm();
			// добавляем сделку
			$custom = [
				['field_id' => 256505, 'values' => [['value' => (string)$this->resale->address,]]], // адрес
				['field_id' => 256507, 'values' => [['value' => (string)$this->resale->total_area,]]], // площадь
				['field_id' => 575864, 'values' => [['value' => (string)$this->resale->price,]]], // цена
				['field_id' => 255849, 'values' => [['value' => (string)$this->resale->floor,]]], // этаж
			];
			$lead_id = $amo->addLeads(Yii::$app->params['AmoCRM']['pipeline_id'], 'Сайт '.$this->type.' '.date('d.m.Y'),$custom);
			if($lead_id) {
				// добавляем примечание
				$text = 'Тип заявки: '. $this->type."\n";
				$text .= 'Имя: '. $this->name."\n";
				$text .= 'Телефон: '. $this->phone."\n";
				$text .= 'Город: '. $this->resale->city->name."\n";
				$text .= 'Категория: '. $category."\n";
				$text .= 'Район: '. $this->resale->area."\n";
				$text .= 'Общая площадь: '. $this->resale->total_area."\n";
				$text .= 'Цена: '. $this->resale->price."\n";
				$text .= 'Этаж: '. $this->resale->floor."\n";
				$text .= 'Адрес: '. $this->resale->address."\n";
				$text .= 'Дата и время: '. date('d.m.Y H:i') . "\n";
				$amo->addLeadsNotes($lead_id,$text);
			}
		}
	}

}
