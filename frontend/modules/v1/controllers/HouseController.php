<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\House;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class HouseController extends RestController {

	public $modelClass = House::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'complex'],
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
		$ret['complex'] = ['GET', 'HEAD'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/house",
	 *     tags={"Дома (house)"},
	 *     summary="Список домов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *	   security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('complex_id,id'),
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
	 * @OA\Get(path="/v1/house/complex/{complex}",
	 *     tags={"Дома (house)"},
	 *     summary="Список домов комплекса",
	 * 	   @OA\Parameter(name="complex", in="path", description="id комплекса", required=true),
	 * 	   @OA\Parameter(name="status", in="query", description="Статус", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 * )
	 */
	public function actionComplex($complex, $status="") {
		$model = new $this->modelClass;
		$query = $model->find();
		$query->alias('h');
		$query->where(['h.complex_id' => $complex,])->orderBy('h.name');
		$query->leftjoin('entrance e', 'e.house_id=h.id');
		$query->leftjoin('tplfloor tf', 'tf.entrance_id=e.id');
		$query->leftjoin('tplapart ta', 'ta.tplfloor_id=tf.id');
		$query->leftjoin('apart a1', 'a1.tplapart_id=ta.id');
		$query->leftjoin('apart a2', 'a2.tplapart_id=ta.id and a2.status=2');
		$query->groupby('h.id');
		if($status) {
			$query->andWhere(['h.status' => $status,]);
		}
		$query->select(['h.*', 'count(distinct a2.id) as aparts_status2', 'count(distinct a1.id) as aparts', 'max(a1.floor) as floors']);
		$query->asArray();
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
	 * @OA\Post(path="/v1/house/create",
	 * 		tags={"Дома (house)"},
	 * 		summary="Добавление дома",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Название(номер)", type="string"),
	 *				@OA\Property(property="complex_id", description="ID комплекса", type="string"),
	 *				@OA\Property(property="plan", description="json-массив координат", type="string"),
	 *				@OA\Property(property="status", description="Статус", type="string"),
	 *				@OA\Property(property="metka", description="Метка", type="string"),
	 *				@OA\Property(property="theme_quest", description="Тема вопросов", type="string"),
	 *				@OA\Property(property="promo", description="Акции (1 или 0)", type="string"),
	 *				@OA\Property(property="counter", description="Счетчик (1 или 0)", type="string"),
	 *				@OA\Property(property="counter_text", description="Счетчик текст", type="string"),
	 *				@OA\Property(property="counter_proc0", description="Счетчик начальный %", type="string"),
	 *				@OA\Property(property="counter_date_begin", description="Счетчик дата начала (unix time)", type="string"),
	 *				@OA\Property(property="counter_date_end", description="Счетчик дата окончания (unix time)", type="string"),
	 *				@OA\Property(property="showprice", description="Показывать цены  (1 или 0)", type="string"),
	 *				@OA\Property(property="tradein", description="Trade in (1 или 0)", type="string"),
	 *				@OA\Property(property="description", description="Описание", type="string"),
	 *				@OA\Property(property="street", description="Улица и дом", type="string"),
	 *				@OA\Property(property="video", description="Видеотрансляция", type="string"),
	 *				@OA\Property(property="virtualtour", description="Виртуальный тур", type="string"),
	 *				@OA\Property(property="instruction", description="Инструкция", type="string"),
	 *				@OA\Property(property="imageplan", description="Схема подъездов", type="string", format="binary"),
	 *				@OA\Property(property="geoposition", description="Гео координаты через запятую", type="string"),
	 *				@OA\Property(property="images_gal[]", description="Изображения галереи", type="string", format="binary"),
	 *				@OA\Property(property="images_view[]", description="Изображения видов", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return House::create(['images_gal' => 'gallery', 'images_view' => 'view',]);
	}

	/**
	 * @OA\Post(path="/v1/house/updateimage/{id}",
	 * 		tags={"Дома (house)"},
	 * 		summary="Изменение схемы подъездов",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="imageplan", description="Изображение", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = House::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/house/update/{id}",
	 * 		tags={"Дома (house)"},
	 * 		summary="Изменение дома",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Название(номер)", required=false),
	 *		@OA\Parameter(name="complex_id", in="query", description="ID комплекса", required=false),
	 *		@OA\Parameter(name="plan", in="query", description="json-массив координат", required=false),
	 *		@OA\Parameter(name="status", in="query", description="Статус", required=false),
	 *		@OA\Parameter(name="metka", in="query", description="Метка", required=false),
	 *		@OA\Parameter(name="theme_quest", in="query", description="Тема вопросов", required=false),
	 *		@OA\Parameter(name="promo", in="query", description="Акции (1 или 0)", required=false),
	 *		@OA\Parameter(name="counter", in="query", description="Счетчик (1 или 0)", required=false),
	 *		@OA\Parameter(name="counter_text", in="query", description="Счетчик текст", required=false),
	 *		@OA\Parameter(name="counter_proc0", in="query", description="Счетчик начальный %", required=false),
	 *		@OA\Parameter(name="counter_date_begin", in="query", description="Счетчик дата начала (unix time)", required=false),
	 *		@OA\Parameter(name="counter_date_end", in="query", description="Счетчик дата окончания (unix time)", required=false),
	 *		@OA\Parameter(name="showprice", in="query", description="Показывать цены  (1 или 0)", required=false),
	 *		@OA\Parameter(name="tradein", in="query", description="Trade in (1 или 0)", required=false),
	 *		@OA\Parameter(name="description", in="query", description="Описание", required=false),
	 *		@OA\Parameter(name="street", in="query", description="Улица и дом", required=false),
	 *		@OA\Parameter(name="video", in="query", description="Видеотрансляция", required=false),
	 *		@OA\Parameter(name="virtualtour", in="query", description="Виртуальный тур", required=false),
	 *		@OA\Parameter(name="instruction", in="query", description="Инструкция", required=false),
	 *		@OA\Parameter(name="imageplan", in="query", description="Схема подъездов", required=false),
	 *		@OA\Parameter(name="geoposition", in="query", description="Гео координаты через запятую", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = House::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/house/delete/{id}",
	 * 		tags={"Дома (house)"},
	 * 		summary="Удаление дома",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = House::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/house/view/{id}",
	 *     tags={"Дома (house)"},
	 *     summary="Просмотр дома по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/House")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = House::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['images'] = \common\models\Media::find()->where(['parent' => 'house', 'id_parent' => $id,])->asArray()->orderBy('order, id')->all();
			$model['entrances'] = \common\models\Entrance::find()->where(['house_id' => $id,])->asArray()->orderBy('name')->all();
			$model['finishings'] = \common\models\Finishing::find()->where(['house_id' => $id,])->asArray()->orderBy('id')->all();
			$parking = \common\models\Parking::find()->where(['house_id' => $id,])->one();
			if($parking) {
				$model['parking_id'] = $parking->id;
			}
			$comrealty = \common\models\Comrealty::find()->where(['house_id' => $id,])->one();
			if($comrealty) {
				$model['comrealty_id'] = $comrealty->id;
			}
			$query = \common\models\Entrance::find()->alias('e')->where(['house_id' => $id,]);
			$query->leftjoin('tplfloor tf', 'tf.entrance_id=e.id');
			$query->leftjoin('tplapart ta', 'ta.tplfloor_id=tf.id');
			$query->leftjoin('apart a1', 'a1.tplapart_id=ta.id');
			$query->leftjoin('apart a2', 'a2.tplapart_id=ta.id and a2.status=2');
			$query->select(['count(distinct a2.id) as aparts_status2', 'count(distinct a1.id) as aparts', 'max(e.floors) as floors']);
			$query->asArray();
			$stat = $query->one();
			$model['aparts_status2'] = $stat['aparts_status2'];
			$model['aparts'] = $stat['aparts'];
			$model['floors'] = $stat['floors'];
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
