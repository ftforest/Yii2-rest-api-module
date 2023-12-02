<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Resale;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ResaleController extends RestController {

	public $modelClass = Resale::class;
	protected $category = 1;

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
	 * @OA\Get(path="/v1/resale",
	 *     tags={"Вторичка (resale)"},
	 *     summary="Список объектов вторичного жилья",
	 * 	   @OA\Parameter(name="city", in="query", description="Идентификатор города", required=false),
	 * 	   @OA\Parameter(name="area", in="query", description="Район", required=false),
	 * 	   @OA\Parameter(name="category", in="query", description="Категория (1-квартиры, 2-коттеджи, 3-земельные участки)", required=false),
	 * 	   @OA\Parameter(name="price_min", in="query", description="Цена нижняя граница", required=false),
	 * 	   @OA\Parameter(name="price_max", in="query", description="Цена верхняя граница", required=false),
	 * 	   @OA\Parameter(name="area_min", in="query", description="Площадь нижняя граница", required=false),
	 * 	   @OA\Parameter(name="area_max", in="query", description="Площадь верхняя граница", required=false),
	 * 	   @OA\Parameter(name="floor_min", in="query", description="Этаж нижняя граница", required=false),
	 * 	   @OA\Parameter(name="floor_max", in="query", description="Этаж верхняя граница", required=false),
	 * 	   @OA\Parameter(name="rooms", in="query", description="Кол-во комнат", required=false),
	 * 	   @OA\Parameter(name="published", in="query", description="Опубликовано (1-да, 0-нет)", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 * )
	 */
	public function actionIndex($city = null, $category = null, $rooms = null, $published = null, $price_min = null, $price_max = null, $area_min = null, $area_max = null, $floor_min = null, $floor_max = null, $area = null ) {
		$model = new $this->modelClass;
		$query = $model->find()->orderBy('city_id, id');
		if($city) {
			$query->andWhere(['city_id' => $city,]);
		}
		$total_area = 'total_area';
		if($category) {
			$query->andWhere(['category' => $category,]);
			if($category==3) {
				$total_area = 'plot_area';
			}
		}
		if($rooms!==null) {
			if($rooms<4) {
				$query->andWhere(['rooms' => $rooms,]);
			} else {
				$query->andWhere('rooms>3');
			}
		}
		if($price_min!==null) {
			$query->andWhere('price>=:price_min', ['price_min' => $price_min,]);
		}
		if($price_max!==null) {
			$query->andWhere('price<=:price_max', ['price_max' => $price_max,]);
		}
		if($area_min!==null) {
			$query->andWhere($total_area.'>=:area_min', ['area_min' => $area_min,]);
		}
		if($area_max!==null) {
			$query->andWhere($total_area.'<=:area_max', ['area_max' => $area_max,]);
		}
		if($floor_min!==null) {
			$query->andWhere('floor>=:floor_min', ['floor_min' => $floor_min,]);
		}
		if($floor_max!==null) {
			$query->andWhere('floor<=:floor_max', ['floor_max' => $floor_max,]);
		}
		if($published!==null) {
			$query->andWhere(['published' => $published,]);
		}
		if($area!==null) {
			$query->andWhere(['area' => $area,]);
		}
		return Resale::addImage($query->asArray()->all());
	}

	/**
	 * @OA\Post(path="/v1/resale/create",
	 * 		tags={"Вторичка (resale)"},
	 * 		summary="Добавление квартиры вторичного жилья",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="city_id", description="ID города", type="string"),
	 *				@OA\Property(property="address", description="Адрес", type="string"),
	 *				@OA\Property(property="rooms", description="Кол-во комнат", type="string"),
	 *				@OA\Property(property="area", description="Район", type="string"),
	 *				@OA\Property(property="coord", description="Координаты", type="string"),
	 *				@OA\Property(property="price", description="Цена", type="string"),
	 *				@OA\Property(property="description", description="Описание", type="string"),
	 *				@OA\Property(property="total_area", description="Общая площадь", type="string"),
	 *				@OA\Property(property="living_area", description="Жилая площадь", type="string"),
	 *				@OA\Property(property="kitchen_area", description="Площадь кухни", type="string"),
	 *				@OA\Property(property="balcony_area", description="Площадь балкона", type="string"),
	 *				@OA\Property(property="floors", description="Этажей всего", type="string"),
	 *				@OA\Property(property="floor", description="Этаж квартиры", type="string"),
	 *				@OA\Property(property="entrance", description="Подъезд", type="string"),
	 *				@OA\Property(property="balcony", description="Тип балкона", type="string"),
	 *				@OA\Property(property="published", description="Опубликовано", type="string"),
	 *				@OA\Property(property="images[]", description="Изображения", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "City not found",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Resale::createcat(['images'=>''], $this->category);
	}

	/**
	 * @OA\Put(path="/v1/resale/update/{id}",
	 * 		tags={"Вторичка (resale)"},
	 * 		summary="Изменение квартиры вторичного жилья",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="city_id", in="query", description="ID города", required=false),
	 * 		@OA\Parameter(name="address", in="query", description="Адрес", required=false),
	 * 		@OA\Parameter(name="rooms", in="query", description="Кол-во комнат", required=false),
	 * 		@OA\Parameter(name="area", in="query", description="Район", required=false),
	 * 		@OA\Parameter(name="coord", in="query", description="Координаты", required=false),
	 * 		@OA\Parameter(name="price", in="query", description="Цена", required=false),
	 * 		@OA\Parameter(name="description", in="query", description="Описание", required=false),
	 * 		@OA\Parameter(name="total_area", in="query", description="Общая площадь", required=false),
	 * 		@OA\Parameter(name="living_area", in="query", description="Жилая площадь", required=false),
	 * 		@OA\Parameter(name="kitchen_area", in="query", description="Площадь кухни", required=false),
	 * 		@OA\Parameter(name="balcony_area", in="query", description="Площадь балкона", required=false),
	 * 		@OA\Parameter(name="floors", in="query", description="Этажей всего", required=false),
	 * 		@OA\Parameter(name="floor", in="query", description="Этаж квартиры", required=false),
	 * 		@OA\Parameter(name="entrance", in="query", description="Подъезд", required=false),
	 * 		@OA\Parameter(name="balcony", in="query", description="Тип балкона", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Resale::find()->where(['id' => $id, 'category' => $this->category,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/resale/delete/{id}",
	 * 		tags={"Вторичка (resale)"},
	 * 		summary="Удаление квартиры вторичного жилья",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Resale::find()->where(['id' => $id, 'category' => $this->category,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/resale/view/{id}",
	 *     tags={"Вторичка (resale)"},
	 *     summary="Просмотр объекта вторичного жилья по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Resale")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Resale::find()->where(['id' => $id,])->asArray()->one();

		if ($model) {
			$model['images'] = \common\models\Media::find()->where(['parent' => 'resale', 'id_parent' => $id,])->asArray()->orderBy('order, id')->all();
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
