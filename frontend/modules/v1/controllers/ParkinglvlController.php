<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Parkinglvl;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ParkinglvlController extends RestController {

	public $modelClass = Parkinglvl::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'index', 'parking'],
		];

		return $behaviors;
	}

    /**
     * {@inheritdoc}
     */
	public function actions() {
		return [
			'options' => [
				'class' => 'yii\rest\OptionsAction',
			],
		];
	}
	
    /**
     * {@inheritdoc}
     */
	public function verbs() {
		$ret = parent::verbs();
		$ret['parking'] = ['GET', 'HEAD'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/parkinglvl",
	 *     tags={"Уровни паркинга (parkinglvl)"},
	 *     summary="Список уровней всех паркингов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('parking_id,name'),
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
	 * @OA\Get(path="/v1/parkinglvl/parking/{parking}",
	 *     tags={"Уровни паркинга (parkinglvl)"},
	 *     summary="Список уровней паркинга",
	 * 	   @OA\Parameter(name="parking", in="path", description="id паркинга", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 * )
	 */
	public function actionParking($parking) {
		$model = new $this->modelClass;
		$query = $model->find()->where(['parking_id' => $parking,])->orderBy('name');
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
	 * @OA\Post(path="/v1/parkinglvl/create",
	 * 		tags={"Уровни паркинга (parkinglvl)"},
	 * 		summary="Добавление уровня паркинга",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Название(номер)", type="string"),
	 *				@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *				@OA\Property(property="parking_id", description="ID паркинга", type="string")
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Parkinglvl::create();
	}

	/**
	 * @OA\Post(path="/v1/parkinglvl/updateimage/{id}",
	 * 		tags={"Уровни паркинга (parkinglvl)"},
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
	 * 			@OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Parkinglvl::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/parkinglvl/update/{id}",
	 * 		tags={"Уровни паркинга (parkinglvl)"},
	 * 		summary="Изменение уровня паркинга",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Название(номер)", required=false),
	 *		@OA\Parameter(name="parking_id", in="query", description="ID паркинга", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Parkinglvl::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/parkinglvl/delete/{id}",
	 * 		tags={"Уровни паркинга (parkinglvl)"},
	 * 		summary="Удаление уровня паркинга",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Parkinglvl::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/parkinglvl/view/{id}",
	 *     tags={"Уровни паркинга (parkinglvl)"},
	 *     summary="Просмотр уровня паркинга по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkinglvl")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Parkinglvl::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['parkingplaces'] = \common\models\Parkingplace::find()->where(['parkinglvl_id' => $id,])->asArray()->orderBy('name')->all();
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
