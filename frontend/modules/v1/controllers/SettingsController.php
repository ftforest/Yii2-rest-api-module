<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Settings;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class SettingsController extends RestController {

	public $modelClass = Settings::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options'],
		];

		return $behaviors;
	}

	public function actions() {
		return [
			'options' => [
				'class' => 'yii\rest\OptionsAction',
			],
		];
	}

	/**
	 * 
	 * @OA\Get(path="/v1/settings",
	 *     tags={"Настройки (settings)"},
	 *     summary="Список настроек",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "Список настроек",
	 *         @OA\Schema(ref = "#/components/schemas/Settings")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Settings")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find(),
					//'pagination' => false
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		if ($provider->getCount() <= 0) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		} else {
			return $provider;
		}
	}

	/**
	 * @OA\Put(path="/v1/settings/update/{id}",
	 * 		tags={"Настройки (settings)"},
	 * 		summary="Изменение параметра",
	 *		@OA\Parameter(name="id", in="path", description="Идентификатор или тип", required=true),
	 *		@OA\Parameter(name="title", in="query", description="Имя параметра", required=false),
	 *		@OA\Parameter(name="value", in="query", description="Значение параметра", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Settings")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Settings::find()->where(['id' => $id])->one();
		if(!$model) {
			$model = Settings::find()->where(['type' => $id])->one();
			if(!$model) {
				throw new \yii\web\HttpException(404, 'No entries found with this query string');
			}
		}
		return $model->upd();
	}

	/**
	 *
	 * @OA\Get(path="/v1/settings/view/{id}",
	 *     tags={"Настройки (settings)"},
	 *     summary="Просмотр параметра",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор или тип параметра", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Settings")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Settings")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionView($id) {
		$model = Settings::find()->where(['id' => $id])->one();
		if(!$model) {
			$model = Settings::find()->where(['type' => $id])->one();
			if(!$model) {
				throw new \yii\web\HttpException(404, 'No entries found with this query string');
			}
		}
		return $model;
	}

	/**
	 * Checks the privilege of the current settings.
	 *
	 * @param string $action the ID of the action to be executed
	 * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
	 * @param array $params additional parameters
	 * @throws ForbiddenHttpException if the settings does not have access
	 */
	public function checkAccess($action, $model = null, $params = []) {
		if ($action === 'update' || $action === 'delete' || $action === 'view' || $action === 'index') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
