<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Promo;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class PromoController extends RestController {

	public $modelClass = Promo::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'published'],
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
	 * @OA\Get(path="/v1/promo",
	 *     tags={"Акции (promo)"},
	 *     summary="Список акций",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *	   security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('order, date_begin'),
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
	 * 
	 * @OA\Get(path="/v1/promo/published",
	 *     tags={"Акции (promo)"},
	 *     summary="Список опубликованных акций",
	 * 	   @OA\Parameter(name="actual", in="query", description="Актуальные сейчас (1-да, 0-все)", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 * )
	 */
	public function actionPublished() {
		$model = new $this->modelClass;
		$query = $model->find()->where(['published' => 1,])->orderBy('order, date_begin');
		$post = Yii::$app->request->post();
		if(empty($post)) {
			$post = Yii::$app->request->get();
		}
		if(isset($post['actual']) && $post['actual']==1) {
			$query->andWhere('date_begin<:t and date_end>:t', ['t' => time(),]);
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
	 * @OA\Post(path="/v1/promo/create",
	 * 		tags={"Акции (promo)"},
	 * 		summary="Добавление акции",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Имя", type="string"),
	 *				@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *				@OA\Property(property="text", description="Содержание", type="string"),
	 *				@OA\Property(property="order", description="Порядок", type="string"),
	 *				@OA\Property(property="date_begin", description="Дата начала (unix time)", type="string"),
	 *				@OA\Property(property="date_end", description="Дата окончания (unix time)", type="string"),
	 *				@OA\Property(property="published", description="Опубликовано", type="string"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Promo::create();
	}

	/**
	 * @OA\Post(path="/v1/promo/updateimage/{id}",
	 * 		tags={"Акции (promo)"},
	 * 		summary="Изменение изображения акции",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Promo::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/promo/update/{id}",
	 * 		tags={"Акции (promo)"},
	 * 		summary="Изменение акции",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 * 		@OA\Parameter(name="slug", in="query", description="Алиас для URL", required=false),
	 * 		@OA\Parameter(name="image", in="query", description="Изображение", required=false),
	 * 		@OA\Parameter(name="text", in="query", description="Содержание", required=false),
	 * 		@OA\Parameter(name="order", in="query", description="Порядок", required=false),
	 * 		@OA\Parameter(name="date_begin", in="query", description="Дата начала (unix time)", required=false),
	 * 		@OA\Parameter(name="date_end", in="query", description="Дата окончания (unix time)", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Promo::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/promo/delete/{id}",
	 * 		tags={"Акции (promo)"},
	 * 		summary="Удаление акции",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Promo::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/promo/view/{id}",
	 *     tags={"Акции (promo)"},
	 *     summary="Просмотр акции по id или slug",
	 *	   @OA\Parameter(name="id", in="path", description="Ид или алиас", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Promo")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Promo::find()->where(['slug' => $id,])->one();
		if($model) {
			$id = $model['id'];
		} elseif(is_numeric($id)) {
			$model = Promo::find()->where(['id' => $id])->one();
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
