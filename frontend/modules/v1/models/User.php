<?php

namespace frontend\modules\v1\models;

use Yii;
use yii\web\HttpException;

/**
 * @OA\Schema(required={"id", "username",})
 *
 * @OA\Property(property="id", type="integer")
 * @OA\Property(property="status", type="integer")
 * @OA\Property(property="username", type="string")
 * @OA\Property(property="surname", type="string")
 * @OA\Property(property="phone", type="string")
 * @OA\Property(property="email", type="string")
 * @OA\Property(property="access_token", type="string")
 * @OA\Property(property="password", type="string")
 * @OA\Property(property="type", type="string")
 */
class User extends \common\models\User {

	use \common\traits\ApiModelTrait;

	public function fields() {
		return [
			'id', 'email', 'username', 'surname', 'status', 'phone', 'access_token', 'type'
		];
	}
	public function extraFields() {
		return [
		];
	}

	/**
	 * Создание пользователя
	 * @return boolean
	 */
	public static function create($medias = []) {
		$post = Yii::$app->request->post();
		if (empty($post)) {
			$post = Yii::$app->request->get();
		}
		unset($post['id']);
		$u = static::findByEmail($post['email']);
		if ($u) {
			throw new HttpException(404, 'Такой пользователь уже есть');
		}
		$user = new parent();
		$user->patronymic = '';
		$user->temporary_pass = '';
		foreach (['email', 'username', 'password'] as $fld) {
			if(!isset($post[$fld]) || !$post[$fld]) {
				throw new HttpException(404, 'Не указано поле '.$fld);
			}
			if($fld=='password') {
				$user->setPassword($post[$fld]);
				$user->generateAuthKey();
				$user->generateAccessToken();
			} else {
				$user->$fld = $post[$fld];
			}
		}
		$user->type = 'other';
		foreach (['type', 'phone', 'surname'] as $fld) {
			if(!isset($post[$fld]) || !$post[$fld]) {
				continue;
			}
			$user->$fld = $post[$fld];
		}
		if(!$user->save()) {
			throw new HttpException(404, 'Не удалось создать пользователя.'."\n".$user->getStringError("\n"));
		}
		$ret = ['id' => $user->id,];
		return $ret;
	}

}
