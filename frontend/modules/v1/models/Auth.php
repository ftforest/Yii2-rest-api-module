<?php

namespace frontend\modules\v1\models;

//use backend\models\rbac;
use Yii;
use yii\base\Model;
use common\models\User;

/**
 * @OA\Schema(required={"email", "password"})
 *
 * @OA\Property(property="email", type="string")
 * @OA\Property(property="password", type="string")
 */
class Auth extends Model {

	public $email;
	public $password;

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['email', 'password'], 'required', 'on' => self::SCENARIO_DEFAULT],
			['password', 'validatePassword', 'on' => self::SCENARIO_DEFAULT],
		];
	}

	public function attributeLabels() {
		return [
			'password' => 'Пароль',
			'email' => 'E-Mail'
		];
	}

	public function validatePassword($attribute, $params) {
		$user = User::findByEmail($this->email);

		if (!$user) {
			$this->addError($attribute, 'Пользовать не найден');
			return false;
		}

		if (!$user->validatePassword($this->password)) {
			$this->addError($attribute, 'Неправильный E-Mail и(или) пароль');
			return false;
		}

		return true;
	}

	/**
	 * Авторизация
	 * @return array
	 */
	public function auth() {
		$post = Yii::$app->request->post();
		if(empty($post)) {
			$post = Yii::$app->request->get();
		}
		$this->attributes = $post;
		
		if (!$this->validate()) {
			Yii::$app->getResponse()->setStatusCode(401);
			return ['status' => false, 'message' => $this->getErrors()];
		}

		$user = User::findByEmail($this->email);

		if ($user != null) {
			$user->scenario = $user::SCENARIO_AUTHORIZATION;
			$user->generateAccessToken();
			$user->save(false, ['access_token']);
			\Yii::$app->user->login($user);

			if (!Yii::$app->user->can($user->type)) {
				//rbac::setRole($user->type, $user->id);
			}

			$user->save();
		} else {
			Yii::$app->getResponse()->setStatusCode(401);
			$user->addError('role', 'Неправильный E-Mail и(или) пароль');
			return ['status' => false, 'message' => $this->getErrors()];
		}

		return ['status' => true, 'token' => $user->access_token];
	}

}
