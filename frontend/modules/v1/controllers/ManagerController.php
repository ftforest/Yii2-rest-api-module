<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Manager;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ManagerController extends RestController {

	public $modelClass = Manager::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'index'],
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
	 * @OA\Get(path="/v1/manager/{city_id}",
	 *     tags={"Менеджеры (manager)"},
	 *     summary="Список менеджеров города",
	 *     @OA\Parameter(name="city_id", in="path", description="Идентификатор города", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 * )
	 */
	public function actionIndex($city_id=0) {
		$model = new $this->modelClass;
		try {
			$query = $model->find();
			if($city_id) {
				$query->where(['city_id' => $city_id,]);
			}
			$provider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => false
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
	 * @OA\Post(path="/v1/manager/create",
	 * 		tags={"Менеджеры (manager)"},
	 * 		summary="Добавление менеджера",
	 * 		@OA\Parameter(name="name", in="query", description="ФИО", required=true),
	 * 		@OA\Parameter(name="city_id", in="query", description="Идентификатор города", required=true),
	 * 		@OA\Parameter(name="category", in="query", description="Категория", required=false),
	 * 		@OA\Parameter(name="phone", in="query", description="Телефон", required=false),
	 * 		@OA\Parameter(name="email", in="query", description="Email", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "City not found",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Manager::create();
	}

	/**
	 * @OA\Put(path="/v1/manager/update/{id}",
	 * 		tags={"Менеджеры (manager)"},
	 * 		summary="Изменение менеджера",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="ФИО", required=false),
	 * 		@OA\Parameter(name="category", in="query", description="Категория", required=false),
	 * 		@OA\Parameter(name="phone", in="query", description="Телефон", required=false),
	 * 		@OA\Parameter(name="email", in="query", description="Email", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Manager::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/manager/delete/{id}",
	 * 		tags={"Менеджеры (manager)"},
	 * 		summary="Удаление менеджера",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Manager::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$id = $model->id;
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/manager/view/{id}",
	 *     tags={"Менеджеры (manager)"},
	 *     summary="Просмотр менеджера",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Manager")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Manager::find()->where(['id' => $id])->one();

		if ($model) {
			return $model;
		}
		throw new \yii\web\HttpException(404, 'No entries found with this query string');
	}

	/**
	 * Checks the privilege of the current user.
	 *
	 * @param string $action the ID of the action to be executed
	 * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
	 * @param array $params additional parameters
	 * @throws ForbiddenHttpException if the user does not have access
	 */
	public function checkAccess($action, $model = null, $params = []) {
		if ($action === 'create' || $action === 'delete' || $action === 'update') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
