<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Review;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ReviewController extends RestController {

	public $modelClass = Review::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'published', 'create'],
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
	 * @OA\Get(path="/v1/review",
	 *     tags={"Отзывы (review)"},
	 *     summary="Список отзывов",
	 * 	   @OA\Parameter(name="published", in="query", description="Опубликовано (1 или 0)", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *	   security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex($published=null) {
		$model = new $this->modelClass;
		$query = $model->find()->orderBy('date_create');
		if($published!==null) {
			$query->where(['published' => (int)$published,]);
		}
		try {
			$provider = new ActiveDataProvider([
				'query' => $query,
					//'pagination' => false
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		return $provider;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/review/published",
	 *     tags={"Отзывы (review)"},
	 *     summary="Список опубликованных отзывов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 * )
	 */
	public function actionPublished() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->where(['published' => 1,])->orderBy('date_create'),
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
	 * @OA\Post(path="/v1/review/create",
	 * 		tags={"Отзывы (review)"},
	 * 		summary="Добавление отзыва",
	 * 		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 * 		@OA\Parameter(name="email", in="query", description="Email", required=false),
	 * 		@OA\Parameter(name="text", in="query", description="Содержание", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 * )
	 */
	public function actionCreate() {
		return Review::create();
	}

	/**
	 * @OA\Put(path="/v1/review/update/{id}",
	 * 		tags={"Отзывы (review)"},
	 * 		summary="Изменение отзыва",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 * 		@OA\Parameter(name="slug", in="query", description="Алиас для URL", required=false),
	 * 		@OA\Parameter(name="email", in="query", description="Email", required=false),
	 * 		@OA\Parameter(name="text", in="query", description="Содержание", required=false),
	 * 		@OA\Parameter(name="date_create", in="query", description="Дата создания (unix time)", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Review::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/review/delete/{id}",
	 * 		tags={"Отзывы (review)"},
	 * 		summary="Удаление отзыва",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Review::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/review/view/{id}",
	 *     tags={"Отзывы (review)"},
	 *     summary="Просмотр отзыва по id или slug",
	 *	   @OA\Parameter(name="id", in="path", description="Ид или алиас", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Review")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Review::find()->where(['slug' => $id,])->one();
		if($model) {
			$id = $model['id'];
		} elseif(is_numeric($id)) {
			$model = Review::find()->where(['id' => $id])->one();
		}

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
		if ($action === 'delete' || $action === 'update' || $action === 'index') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
