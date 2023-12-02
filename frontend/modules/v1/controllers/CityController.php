<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\City;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class CityController extends RestController {

	public $modelClass = City::class;

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
	 * @OA\Get(path="/v1/city",
	 *     tags={"Города (city)"},
	 *     summary="Список городов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "Список городов",
	 *         @OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/City")
	 *     ),
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
	 * @OA\Post(path="/v1/city/create",
	 * 		tags={"Города (city)"},
	 * 		summary="Добавление города",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Наименование", type="string"),
	 *				@OA\Property(property="info", description="Информация", type="string"),
	 *				@OA\Property(property="phone", description="Телефон", type="string"),
	 *				@OA\Property(property="map", description="Карта", type="string"),
	 *				@OA\Property(property="qr", description="QR код", type="string", format="binary"),
	 *				@OA\Property(property="video1", description="Видео фона", type="string", format="binary"),
	 *				@OA\Property(property="video2", description="Видео фона в ночном режиме", type="string", format="binary"),
	 *				@OA\Property(property="background1", description="Изображение фона", type="string", format="binary"),
	 *				@OA\Property(property="background2", description="Изображение фона в ночном режиме", type="string", format="binary"),
	 *				@OA\Property(property="iswhite", description="Белое меню для ночного режима (1 или 0)", type="string"),
	 *				@OA\Property(property="isnight", description="Другой фон для ночного режима (1 или 0)", type="string"),
	 *				@OA\Property(property="published", description="Опубликовано", type="string"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return City::create();
	}

	/**
	 * @OA\Put(path="/v1/city/update/{id}",
	 * 		tags={"Города (city)"},
	 * 		summary="Изменение города",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Наименование", required=false),
	 * 		@OA\Parameter(name="info", in="query", description="Информация", required=false),
	 * 		@OA\Parameter(name="phone", in="query", description="Телефон", required=false),
	 * 		@OA\Parameter(name="map", in="query", description="Карта", required=false),
	 * 		@OA\Parameter(name="iswhite", in="query", description="Белое меню для ночного режима (1 или 0)", required=false),
	 * 		@OA\Parameter(name="isnight", in="query", description="Другой фон для ночного режима (1 или 0)", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = City::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Post(path="/v1/city/updateimage/{id}",
	 * 		tags={"Города (city)"},
	 * 		summary="Изменение изображений",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="qr", description="QR код", type="string", format="binary"),
	 *					@OA\Property(property="video1", description="Видео фона", type="string", format="binary"),
	 *					@OA\Property(property="video2", description="Видео фона в ночном режиме", type="string", format="binary"),
	 *					@OA\Property(property="background1", description="Изображение фона", type="string", format="binary"),
	 *					@OA\Property(property="background2", description="Изображение фона в ночном режиме", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Tplapart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Tplapart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = City::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	
	/**
	 * @OA\Delete(path="/v1/city/delete/{id}",
	 * 		tags={"Города (city)"},
	 * 		summary="Удаление города",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = City::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$id = $model->id;
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/city/{id}",
	 *     tags={"Города (city)"},
	 *     summary="Просмотр города",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/City")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = City::find()->where(['id' => $id])->one();

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
		if ($action === 'create' || $action === 'delete' || $action === 'update' || $action === 'updateimage') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
