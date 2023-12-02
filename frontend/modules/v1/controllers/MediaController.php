<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Media;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class MediaController extends RestController {

	public $modelClass = Media::class;

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
	 * @OA\Get(path="/v1/media",
	 *     tags={"Изображения (media)"},
	 *     summary="Список изображений",
	 * 		@OA\Parameter(name="parent", in="query", description="Родитель", required=false),
	 * 		@OA\Parameter(name="id_parent", in="query", description="ID родителя", required=false),
	 * 		@OA\Parameter(name="type", in="query", description="Тип изображения (галерея, вид и т.д.)", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		$query = $model->find()->orderBy('order, id');
		$post = Yii::$app->request->post();
		if (empty($post)) {
			$post = Yii::$app->request->get();
		}
		if (isset($post['parent'])) {
			$query->andWhere(['parent' => $post['parent'],]);
		}
		if (isset($post['type'])) {
			$query->andWhere(['type' => $post['type'],]);
		}
		if (isset($post['id_parent'])) {
			$query->andWhere(['id_parent' => $post['id_parent'],]);
		}
		try {
			$provider = new ActiveDataProvider([
				'query' => $query,
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
	 * @OA\Post(path="/v1/media/create",
	 * 		tags={"Изображения (media)"},
	 * 		summary="Добавление изображения",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 * 					@OA\Property(property="parent", description="Родитель", type="string"),
	 * 					@OA\Property(property="id_parent", description="ID родителя", type="string"),
	 * 					@OA\Property(property="type", description="Тип изображения (галерея, вид и т.д.)", type="string"),
	 * 					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 * 					@OA\Property(property="published", description="Опубликовано", type="string"),
	 * 					@OA\Property(property="order", description="Порядок", type="string"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Media::create();
	}

	/**
	 * @OA\Post(path="/v1/media/updateimage/{id}",
	 * 		tags={"Изображения (media)"},
	 * 		summary="Изменение файла изображения",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 * 					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 * 					@OA\Property(property="order", description="Порядок", type="string"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Media::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/media/update/{id}",
	 * 		tags={"Изображения (media)"},
	 * 		summary="Изменение изображения",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="parent", in="query", description="Родитель", required=false),
	 * 		@OA\Parameter(name="id_parent", in="query", description="ID родителя", required=false),
	 * 		@OA\Parameter(name="type", in="query", description="Тип изображения (галерея, вид и т.д.)", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Parameter(name="order", in="query", description="Порядок", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Media::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/media/delete/{id}",
	 * 		tags={"Изображения (media)"},
	 * 		summary="Удаление изображения",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Media::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/media/{id}",
	 *     tags={"Изображения (media)"},
	 *     summary="Просмотр изображения по id",
	 * 	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Media")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Media::find()->where(['id' => $id,])->one();
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
		if ($action === 'create' || $action === 'delete' || $action === 'update' || $action === 'index') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
