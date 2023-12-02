<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Requestcall;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class RequestcallController extends RestController {

	public $modelClass = Requestcall::class;

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
	 * @OA\Get(path="/v1/requestcall",
	 *     tags={"Заявки (Request)"},
	 *     summary="Список запросов звонка",
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex($city_id = null) {
		$model = new $this->modelClass;
		$query =  $model->find();
		$query->alias('a');
		$query->leftjoin('city', 'a.city_id=city.id');
		if($city_id!==null) {
			$query->andWhere(['a.city_id' => $city_id,]);
		}
		$query->select(['a.*', 'city.name as city']);
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
	 * @OA\Post(path="/v1/requestcall/create",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Добавление запроса звонка",
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=true),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=true),
	 *		@OA\Parameter(name="city_id", in="query", description="ID города", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 * )
	 */
	public function actionCreate() {
		// Удаление старых заявок (старше 90 дней)
		if(rand(1, 10)>90) {
			// в каждом 10-м случае удаляем старые заявки, чтобы крон не задействовать
			$time = time()-60*60*24*90;
			\common\models\Requestcall::deleteAll('date_create<:t', ['t' => $time,]);
		}
		return Requestcall::create();
	}

	/**
	 * @OA\Put(path="/v1/requestcall/update/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Изменение запроса звонка",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="type", in="query", description="Тип заявки", required=false),
	 *		@OA\Parameter(name="name", in="query", description="Имя", required=false),
	 *		@OA\Parameter(name="phone", in="query", description="телефон", required=false),
	 *		@OA\Parameter(name="city_id", in="query", description="ID города", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Requestcall::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/requestcall/delete/{id}",
	 * 		tags={"Заявки (Request)"},
	 * 		summary="Удаление запроса звонка",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Requestcall::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/requestcall/view/{id}",
	 *     tags={"Заявки (Request)"},
	 *     summary="Просмотр запроса звонка по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Requestcall")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$query =  Requestcall::find();
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
