<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Requesthouse;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class RequesthouseController extends RestController {

	public $modelClass = Requesthouse::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'create'],
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
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/requesthouse",
	 *     tags={"Заявки (Request)"},
	 *     summary="Список заявок по экскурсиям",
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 * 	   @OA\Parameter(name="complex_id", in="query", description="ИД ЖК", required=false),
	 * 	   @OA\Parameter(name="house_id", in="query", description="ИД дома", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex($city_id = null, $complex_id = null, $house_id = null) {
		$model = new $this->modelClass;
		$query =  $model->find();
		$query->alias('a');
		$query->leftjoin('house h', 'a.house_id=h.id');
		$query->leftjoin('complex c', 'h.complex_id=c.id');
		$query->leftjoin('city', 'c.city_id=city.id');
		if($city_id!==null) {
			$query->andWhere(['c.city_id' => $city_id,]);
		}
		if($complex_id!==null) {
			$query->andWhere(['c.id' => $complex_id,]);
		}
		if($house_id!==null) {
			$query->andWhere(['a.house_id' => $house_id,]);
		}
		$query->select(['a.*', 'city.name as city', 'c.name as complex', 'h.name as house']);
		$query->asArray();
		try {
			$provider = new ActiveDataProvider([
				'query' => $query,
//				'pagination' => false,
				'sort' => [
					'enableMultiSort' => true
				]
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		return $provider;
	}

	/**
	 * @OA\Post(path="/v1/requesthouse/create",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Добавление заявки по экскурсиям",
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=true),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=true),
	 *		@OA\Parameter(name="house_id", in="query", description="ID дома", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 * )
	 */
	public function actionCreate() {
		// Удаление старых заявок (старше 90 дней)
		if(rand(1, 10)>90) {
			// в каждом 10-м случае удаляем старые заявки, чтобы крон не задействовать
			$time = time()-60*60*24*90;
			\common\models\Requesthouse::deleteAll('date_create<:t', ['t' => $time,]);
		}
		return Requesthouse::create();
	}

	/**
	 * @OA\Put(path="/v1/requesthouse/update/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Изменение заявки по экскурсиям",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=false),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=false),
	 *		@OA\Parameter(name="house_id", in="query", description="ID дома", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Requesthouse::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/requesthouse/delete/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Удаление заявки по экскурсиям",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Requesthouse::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/requesthouse/view/{id}",
	 *     tags={"Заявки (Request)"},
	 *     summary="Просмотр заявки по экскурсиям по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requesthouse")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$query =  Requesthouse::find();
		$query->where(['id' => $id]);
		$model = $query->one();
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
