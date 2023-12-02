<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\Schema(required={"id",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="published", type="integer")
 */
class Poll extends \common\models\Poll {

	use \common\traits\ApiModelTrait;
	
	public function fields() {
		return [
			'id', 
			'name', 
			'published', 
			'answers'
			];
	}

	public function extraFields() {
		return [
		];
	}
	
	/**
	 * Изменение записи
	 * @return boolean
	 */
	public function upd() {
		$post = Yii::$app->request->post();
		if(empty($post)) {
			$post = Yii::$app->request->get();
		}
		unset($post['id']);
		unset($post['published']);
		foreach ($post as $key => $value) {
			if ($this->hasAttribute($key)) {
				if($value!==NULL) {
					$this->$key = $value;
				}
			}
		}
		return $this->save();
	}
	
	/**
	 * Изменение statusa
	 * @return boolean
	 */
	public function setStatus($publish) {
		if($publish==1) {
			static::updateAll(['published' => '0',]);
		}
		$this->published = $publish;
		return $this->save();
	}
	
	/**
	 * Создание ответа
	 * @return boolean
	 */
	public function createanswer($name) {
		if(!$name) {
			return false;
		}
		$answ = new \common\models\Answer();
		$answ->poll_id = $this->id;
		$answ->name = $name;
		if($answ->save()) {
			return ['id' => $answ->id];
		} else {
			return false;
		}
	}
	
	/**
	 * Проверка данных. 
	 * @param array $data
	 * @return type
	 * @throws \yii\web\HttpException
	 */
	protected static function validData(&$data) {
		if (empty($data)) {
			throw new \yii\web\HttpException(404, 'Tplapart not found');
		}
		unset($data['published']);
		return;
	}
	
}
