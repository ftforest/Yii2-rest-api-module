<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Parkingplace;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ParkingplaceController extends RestController {

	public $modelClass = Parkingplace::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'parkinglvl', 'index'],
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
		$ret['parkinglvl'] = ['GET', 'HEAD'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/parkingplace",
	 *     tags={"Парковочные места (parkingplace)"},
	 *     summary="Список парковочных мест",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('parkinglvl_id,id'),
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
	 * @OA\Get(path="/v1/parkingplace/parkinglvl/{parkinglvl}",
	 *     tags={"Парковочные места (parkingplace)"},
	 *     summary="Список парковочных мест одного уровня паркинга",
	 * 	   @OA\Parameter(name="parkinglvl", in="path", description="id уровня паркинга", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 * )
	 */
	public function actionParkinglvl($parkinglvl) {
		$model = new $this->modelClass;
		$query = $model->find()->where(['parkinglvl_id' => $parkinglvl,])->orderBy('id');
		try {
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
	 * @OA\Post(path="/v1/parkingplace/create",
	 * 		tags={"Парковочные места (parkingplace)"},
	 * 		summary="Добавление парковочного места",
	 *		@OA\Parameter(name="parkinglvl_id", in="query", description="ID уровня паркинга", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Наименование (номер места)", required=false),
	 *		@OA\Parameter(name="plan", in="query", description="json-массив координат", required=false),
	 *		@OA\Parameter(name="square", in="query", description="Площадь", required=false),
	 *		@OA\Parameter(name="price", in="query", description="Цена", required=false),
	 *		@OA\Parameter(name="status", in="query", description="Статус", required=false),
	 *		@OA\Parameter(name="id_1s", in="query", description="ИД 1С", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Parkingplace::create();
	}

	/**
	 * @OA\Put(path="/v1/parkingplace/update/{id}",
	 * 		tags={"Парковочные места (parkingplace)"},
	 * 		summary="Изменение парковочного места",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="parkinglvl_id", in="query", description="ID уровня паркинга", required=false),
	 *		@OA\Parameter(name="name", in="query", description="Наименование (номер места)", required=false),
	 *		@OA\Parameter(name="plan", in="query", description="json-массив координат", required=false),
	 *		@OA\Parameter(name="square", in="query", description="Площадь", required=false),
	 *		@OA\Parameter(name="price", in="query", description="Цена", required=false),
	 *		@OA\Parameter(name="status", in="query", description="Статус", required=false),
	 *		@OA\Parameter(name="id_1s", in="query", description="ИД 1С", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Parkingplace::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/parkingplace/delete/{id}",
	 * 		tags={"Парковочные места (parkingplace)"},
	 * 		summary="Удаление парковочного места",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Parkingplace::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/parkingplace/view/{id}",
	 *     tags={"Парковочные места (parkingplace)"},
	 *     summary="Просмотр парковочного места по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Parkingplace")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Parkingplace::find()->where(['id' => $id])->asArray()->one();

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
