<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Requestapart;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class RequestapartController extends RestController {

	public $modelClass = Requestapart::class;

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
	 * @OA\Get(path="/v1/requestapart",
	 *     tags={"Заявки (Request)"},
	 *     summary="Список заявок по новостройкам",
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 * 	   @OA\Parameter(name="complex_id", in="query", description="ИД ЖК", required=false),
	 * 	   @OA\Parameter(name="house_id", in="query", description="ИД дома", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex($city_id = null, $complex_id = null, $house_id = null) {
		$model = new $this->modelClass;
		$query =  $model->find();
		$query->alias('a');
		$query->leftjoin('apart', 'a.apart_id=apart.id');
		$query->leftjoin('tplapart ta', 'apart.tplapart_id=ta.id');
		$query->leftjoin('tplfloor tf', 'ta.tplfloor_id=tf.id');
		$query->leftjoin('entrance e', 'tf.entrance_id=e.id');
		$query->leftjoin('house h', 'e.house_id=h.id');
		$query->leftjoin('complex c', 'h.complex_id=c.id');
		$query->leftjoin('city', 'c.city_id=city.id');
		if($city_id!==null) {
			$query->andWhere(['c.city_id' => $city_id,]);
		}
		if($complex_id!==null) {
			$query->andWhere(['c.id' => $complex_id,]);
		}
		if($house_id!==null) {
			$query->andWhere(['e.house_id' => $house_id,]);
		}
		$query->select(['a.*', 'city.name as city', 'c.name as complex', 'h.name as house', 'e.name as entrance', 'apart.floor', 'apart.name as apart_num']);
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
	 * @OA\Post(path="/v1/requestapart/create",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Добавление заявки по новостройкам",
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=true),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=true),
	 *		@OA\Parameter(name="apart_id", in="query", description="ID квартиры", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 * )
	 */
	public function actionCreate() {
		// Удаление старых заявок (старше 90 дней)
		if(rand(1, 10)>90) {
			// в каждом 10-м случае удаляем старые заявки, чтобы крон не задействовать
			$time = time()-60*60*24*90;
			\common\models\Requestapart::deleteAll('date_create<:t', ['t' => $time,]);
		}
		return Requestapart::create();
	}

	/**
	 * @OA\Put(path="/v1/requestapart/update/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Изменение заявки по новостройкам",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=false),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=false),
	 *		@OA\Parameter(name="apart_id", in="query", description="ID квартиры", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Requestapart::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/requestapart/delete/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Удаление заявки по новостройкам",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Requestapart::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/requestapart/view/{id}",
	 *     tags={"Заявки (Request)"},
	 *     summary="Просмотр заявки по новостройкам по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestapart")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$query =  Requestapart::find();
/*		$query->alias('a');
		$query->where(['a.id' => $id]);
		$query->leftjoin('apart', 'a.apart_id=apart.id');
		$query->leftjoin('tplapart ta', 'apart.tplapart_id=ta.id');
		$query->leftjoin('tplfloor tf', 'ta.tplfloor_id=tf.id');
		$query->leftjoin('entrance e', 'tf.entrance_id=e.id');
		$query->select(['a.*', 'e.id as entrance_id', 'e.name as entrance_name', 'e.house_id', 'ta.image2d', 'ta.image3d']);
		$query->asArray();
*/
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
