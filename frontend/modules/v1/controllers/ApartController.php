<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Apart;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ApartController extends RestController {

	public $modelClass = Apart::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'entrance', 'index'],
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
		$ret['entrance'] = ['GET', 'HEAD'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/apart",
	 *     tags={"Квартиры (Apart)"},
	 *     summary="Список квартир",
	 * 	   @OA\Parameter(name="sort", in="query", description="Сортировка, например,'price,-id'", required=false),
	 * 	   @OA\Parameter(name="rooms", in="query", description="Кол-во комнат", required=false),
	 * 	   @OA\Parameter(name="area", in="query", description="Район (допускается массив в формате json)", required=false),
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 * 	   @OA\Parameter(name="house_id", in="query", description="ИД дома", required=false),
	 * 	   @OA\Parameter(name="price_min", in="query", description="Цена нижняя граница", required=false),
	 * 	   @OA\Parameter(name="price_max", in="query", description="Цена верхняя граница", required=false),
	 * 	   @OA\Parameter(name="area_min", in="query", description="Площадь нижняя граница", required=false),
	 * 	   @OA\Parameter(name="area_max", in="query", description="Площадь верхняя граница", required=false),
	 * 	   @OA\Parameter(name="floor_min", in="query", description="Этаж нижняя граница", required=false),
	 * 	   @OA\Parameter(name="floor_max", in="query", description="Этаж верхняя граница", required=false),
	 * 	   @OA\Parameter(name="status", in="query", description="Статусы через запятую", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 * )
	 */
	public function actionIndex($rooms = null, $city_id = null, $house_id = null, $price_min = null, $price_max = null, $area_min = null, $area_max = null, $floor_min = null, $floor_max = null, $area = null, $status = null) {
		$model = new $this->modelClass;
		$query =  $model->find();
		$query->alias('a');
		if($rooms!==null) {
			if($rooms<4) {
				$query->andWhere(['a.rooms' => $rooms,]);
			} else {
				$query->andWhere('a.rooms>3');
			}
		}
		$total_area = 'a.square';
		if($price_min!==null) {
			$query->andWhere('a.price>=:price_min', ['price_min' => $price_min,]);
		}
		if($price_max!==null) {
			$query->andWhere('a.price<=:price_max', ['price_max' => $price_max,]);
		}
		if($area_min!==null) {
			$query->andWhere($total_area.'>=:area_min', ['area_min' => $area_min,]);
		}
		if($area_max!==null) {
			$query->andWhere($total_area.'<=:area_max', ['area_max' => $area_max,]);
		}
		if($floor_min!==null) {
			$query->andWhere('a.floor>=:floor_min', ['floor_min' => $floor_min,]);
		}
		if($floor_max!==null) {
			$query->andWhere('a.floor<=:floor_max', ['floor_max' => $floor_max,]);
		}
		$query->leftjoin('tplapart ta', 'a.tplapart_id=ta.id');
		$query->leftjoin('tplfloor tf', 'ta.tplfloor_id=tf.id');
		$query->leftjoin('entrance e', 'tf.entrance_id=e.id');
		$query->leftjoin('house h', 'e.house_id=h.id');
		$query->leftjoin('complex c', 'h.complex_id=c.id');
		if($area!==null || $house_id!==null) {
			if($area!==null) {
				$ar = json_decode($area, TRUE);
				if($ar && is_array($ar)) {
					$area = $ar;
				}
				$query->andWhere(['c.area' => $area,]);
			}
			if($house_id!==null) {
				$query->andWhere(['e.house_id' => $house_id,]);
			}
		}
		if($city_id!==null) {
			$query->andWhere(['c.city_id' => $city_id,]);
		}
		if($status !== null) {
			$statuses = explode(',', $status);
			$query->andWhere(['a.status' => $statuses,]);
		}
		$query->select(['a.*', 'c.name as complex_name', 'c.area as complex_area', 'h.name as house_name', 'e.name as entrance_name', 'ta.image2d', 'ta.image3d', 'concat(a.floor,"/",e.floors) as floors', 'h.showprice']);
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
	 * 
	 * @OA\Get(path="/v1/apart/entrance/{entrance}",
	 *     tags={"Квартиры (Apart)"},
	 *     summary="Список квартир подъезда",
	 * 	   @OA\Parameter(name="entrance", in="path", description="ID подъезда", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 * )
	 */
	public function actionEntrance($entrance) {
		$model = new $this->modelClass;
		$query = $model->find();
		$query->alias('a');
		$query->leftjoin('tplapart ta', 'a.tplapart_id=ta.id');
		$query->leftjoin('tplfloor tf', 'ta.tplfloor_id=tf.id');
		$query->leftjoin('entrance e', 'tf.entrance_id=e.id');
		$query->leftjoin('house h', 'e.house_id=h.id');
		$query->where(['tf.entrance_id' => $entrance,])->orderBy('name');
		$query->select(['a.*', 'ta.image2d', 'ta.image3d', 'h.showprice']);
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
	 * @OA\Post(path="/v1/apart/create",
	 * 		tags={"Квартиры (Apart)"},
	 * 		summary="Добавление квартиры",
	 *		@OA\Parameter(name="name", in="query", description="Название(номер)", required=false),
	 *		@OA\Parameter(name="tplapart_id", in="query", description="ID квартиры шаблона", required=false),
	 *		@OA\Parameter(name="square", in="query", description="Площадь", required=false),
	 *		@OA\Parameter(name="floor", in="query", description="Этаж", required=false),
	 *		@OA\Parameter(name="rooms", in="query", description="Комнат", required=false),
	 *		@OA\Parameter(name="price", in="query", description="Цена", required=false),
	 *		@OA\Parameter(name="id_1s", in="query", description="ID 1С", required=false),
	 *		@OA\Parameter(name="status", in="query", description="Статус", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Apart::create();
	}

	/**
	 * @OA\Put(path="/v1/apart/update/{id}",
	 * 		tags={"Квартиры (Apart)"},
	 * 		summary="Изменение квартиры",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Название(номер)", required=false),
	 *		@OA\Parameter(name="tplapart_id", in="query", description="ID квартиры шаблона", required=false),
	 *		@OA\Parameter(name="square", in="query", description="Площадь", required=false),
	 *		@OA\Parameter(name="floor", in="query", description="Этаж", required=false),
	 *		@OA\Parameter(name="rooms", in="query", description="Комнат", required=false),
	 *		@OA\Parameter(name="price", in="query", description="Цена", required=false),
	 *		@OA\Parameter(name="id_1s", in="query", description="ID 1С", required=false),
	 *		@OA\Parameter(name="status", in="query", description="Статус", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Apart::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/apart/delete/{id}",
	 * 		tags={"Квартиры (Apart)"},
	 * 		summary="Удаление квартиры",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Apart::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/apart/view/{id}",
	 *     tags={"Квартиры (Apart)"},
	 *     summary="Просмотр квартиры по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Apart")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$query =  Apart::find();
		$query->alias('a');
		$query->where(['a.id' => $id]);
		$query->leftjoin('tplapart ta', 'a.tplapart_id=ta.id');
		$query->leftjoin('tplfloor tf', 'ta.tplfloor_id=tf.id');
		$query->leftjoin('entrance e', 'tf.entrance_id=e.id');
		$query->leftjoin('house h', 'e.house_id=h.id');
		$query->leftjoin('complex c', 'h.complex_id=c.id');
		$query->select(['a.*', 'e.id as entrance_id', 'e.name as entrance_name', 'e.house_id', 'ta.image2d', 'ta.image3d', 'h.showprice', 'c.area as complex_area', 'concat(a.floor,"/",e.floors) as floors']);
		$query->asArray();

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
