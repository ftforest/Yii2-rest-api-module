<?php

namespace frontend\modules\v1\models;

use Yii;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     name="bearerAuth",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 *  
 * @OA\Info(
 *     version="1.0",
 *     title="Domkor",
 * )
 */

/**
 * @OA\Schema(required={"name", "short", "content", "title", "meta_description", "meta_keywords", "published"})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="published", type="integer")
 * @OA\Property(property="name", type="string")
 * @OA\Property(property="short", type="string")
 * @OA\Property(property="content", type="string")
 * @OA\Property(property="title", type="string")
 * @OA\Property(property="meta_description", type="string")
 * @OA\Property(property="meta_keywords", type="string")
 */
class Page extends \common\models\Page {

	public function extraFields() {
		return [
		];
	}

	/**
	 * Создание страницы
	 * @param string $short
	 * @return boolean
	 */
	public static function create() {
		$post = Yii::$app->request->post();
		if(empty($post)) {
			$post = Yii::$app->request->get();
		}
		$short = $post['short'];
		$page = static::find()->where(['short' => $short,])->one();
		if ($page) {
			return $page->upd();
		}
		$page = new static();
		foreach ($post as $key => $value) {
			if ($page->hasAttribute($key)) {
				$page->$key = $value;
			}
		}
		$page->short = $short;
		return $page->save(FALSE);
	}

	/**
	 * Изменение страницы
	 * @return boolean
	 */
	public function upd() {
		$post = Yii::$app->request->post();
		if(empty($post)) {
			$post = Yii::$app->request->get();
		}
		foreach ($post as $key => $value) {
			if ($this->hasAttribute($key)) {
				if($value!==NULL) {
					$this->$key = $value;
				}
			}
		}
		return $this->save(FALSE);
	}

}
