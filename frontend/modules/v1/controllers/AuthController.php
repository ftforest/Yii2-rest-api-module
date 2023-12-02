<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Auth;
use Yii;

class AuthController extends RestController {

	public $modelClass = Auth::class;

	public function actions() {
		return [
			'options' => [
				'class' => 'yii\rest\OptionsAction',
			],
		];
	}

	public function verbs() {
		$verbs = parent::verbs();
		$verbs['student'] = ['POST', 'OPTIONS'];
		return $verbs;
	}

	/**
	 * @OA\Post(path="/v1/auth",
	 *     tags={"Авторизация (auth)"},
	 *     summary="Авторизация",
	 *     @OA\Parameter(
	 *         name="email",
	 *         in="query",
	 *         description="Email",
	 *         required=true,
	 *     ),
	 *     @OA\Parameter(
	 *         name="password",
	 *         in="query",
	 *         description="Пароль",
	 *         required=true,
	 *     ),
	 *     @OA\Response(
	 *         response = 200,
	 *         description = "Авторизация пользователя",
	 *         @OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *     @OA\Response(
	 *         response = 401,
	 * 		   description = "Неправильный E-Mail и(или) пароль",
	 *         @OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 * )
	 */
	public function actionCreate() {
		$model = new Auth();
		return $model->auth();
	}
}
