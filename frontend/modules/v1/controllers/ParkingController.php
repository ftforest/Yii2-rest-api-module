<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Parking;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ParkingController extends RestController {

	public $modelClass = Parking::class;

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
	 * @OA\Get(path="/v1/parking",
	 *     tags={"Паркинги (parking)"},
	 *     summary="Список паркингов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('city_id, name'),
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
	 * @OA\Post(path="/v1/parking/create",
	 * 		tags={"Паркинги (parking)"},
	 * 		summary="Добавление паркинга",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Наименование", type="string"),
	 *				@OA\Property(property="brief", description="Краткое описание", type="string"),
	 *				@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *		 		@OA\Property(property="city_id", description="ID города", type="string"),
	 *				@OA\Property(property="coord", description="Координаты на карте", type="string"),
	 *				@OA\Property(property="house_id", description="ID дома", type="string"),
	 *				@OA\Property(property="description", description="Описание", type="string"),
	 *				@OA\Property(property="images[]", description="Изображения галереи", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "City not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Parking::create(['images'=>'']);
	}

	/**
	 * @OA\Post(path="/v1/parking/updateimage/{id}",
	 * 		tags={"Паркинги (parking)"},
	 * 		summary="Изменение изображения",
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
	 * 			@OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Parking::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/parking/update/{id}",
	 * 		tags={"Паркинги (parking)"},
	 * 		summary="Изменение паркинга",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Наименование", required=false),
	 * 		@OA\Parameter(name="brief", in="query", description="Краткое описание", required=false),
	 * 		@OA\Parameter(name="city_id", in="query", description="ID города", required=false),
	 * 		@OA\Parameter(name="coord", in="query", description="Координаты на карте", required=false),
	 * 		@OA\Parameter(name="house_id", in="query", description="ID дома", required=false),
	 * 		@OA\Parameter(name="description", in="query", description="Описание", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Parking::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/parking/delete/{id}",
	 * 		tags={"Паркинги (parking)"},
	 * 		summary="Удаление паркинга",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Parking::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/parking/view/{id}",
	 *     tags={"Паркинги (parking)"},
	 *     summary="Просмотр паркинга по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parking")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Parking::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['images'] = \common\models\Media::find()->where(['parent' => 'parking', 'id_parent' => $id,])->asArray()->orderBy('order, id')->all();
			$model['parkinglvls'] = \common\models\Parkinglvl::find()->where(['parking_id' => $id,])->asArray()->orderBy('name')->all();
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
