<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Requestresale;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class RequestresaleController extends RestController {

	public $modelClass = Requestresale::class;

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
	 * @OA\Get(path="/v1/requestresale",
	 *     tags={"Заявки (Request)"},
	 *     summary="Список заявок по вторичке",
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 * 	   @OA\Parameter(name="address", in="query", description="Адрес (полностью или фрагмент)", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex($city_id = null, $address = null) {
		$model = new $this->modelClass;
		$query =  $model->find();
		$query->alias('a');
		$query->leftjoin('resale', 'a.resale_id=resale.id');
		$query->leftjoin('city', 'resale.city_id=city.id');
		if($city_id!==null) {
			$query->andWhere(['resale.city_id' => $city_id,]);
		}
		if($address!==null) {
			$query->andWhere(['like', 'resale.address', $address]);
		}
		$query->select(['a.*', 'city.name as city', 'resale.area', 'resale.total_area', 'resale.price', 'resale.floor', 'resale.address', ]);
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
	 * @OA\Post(path="/v1/requestresale/create",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Добавление заявки по вторичке",
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=true),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=true),
	 *		@OA\Parameter(name="resale_id", in="query", description="ID объекта вторички", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 * )
	 */
	public function actionCreate() {
		// Удаление старых заявок (старше 90 дней)
		if(rand(1, 10)>90) {
			// в каждом 10-м случае удаляем старые заявки, чтобы крон не задействовать
			$time = time()-60*60*24*90;
			\common\models\Requestresale::deleteAll('date_create<:t', ['t' => $time,]);
		}
		return Requestresale::create();
	}

	/**
	 * @OA\Put(path="/v1/requestresale/update/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Изменение заявки по вторичке",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=false),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=false),
	 *		@OA\Parameter(name="resale_id", in="query", description="ID объекта вторички", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Requestresale::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/requestresale/delete/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Удаление заявки по вторичке",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Requestresale::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/requestresale/view/{id}",
	 *     tags={"Заявки (Request)"},
	 *     summary="Просмотр заявки по вторичке по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestresale")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$query =  Requestresale::find();
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
