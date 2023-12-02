<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Office;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class OfficeController extends RestController {

	public $modelClass = Office::class;

	/**
	 * {@inheritdoc}
	 */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'comrealty', 'index'],
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
		$ret['comrealty'] = ['GET', 'HEAD'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/office",
	 *     tags={"Офисы (office)"},
	 *     summary="Список офисов",
	 * 	   @OA\Parameter(name="area", in="query", description="Район (допускается массив в формате json)", required=false),
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 * 	   @OA\Parameter(name="price_min", in="query", description="Цена нижняя граница", required=false),
	 * 	   @OA\Parameter(name="price_max", in="query", description="Цена верхняя граница", required=false),
	 * 	   @OA\Parameter(name="area_min", in="query", description="Площадь нижняя граница", required=false),
	 * 	   @OA\Parameter(name="area_max", in="query", description="Площадь верхняя граница", required=false),
	 * 	   @OA\Parameter(name="status", in="query", description="Статусы через запятую", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * )
	 */
	public function actionIndex($city_id = null, $price_min = null, $price_max = null, $area_min = null, $area_max = null, $area = null, $status = null) {
		$model = new $this->modelClass;
		/* @var $query yii\db\ActiveQuery */
		$query = $model->find()->orderBy('comrealty_id,office.name');
		$total_area = 'square';
		if ($price_min !== null) {
			$query->andWhere('price>=:price_min', ['price_min' => $price_min,]);
		}
		if ($price_max !== null) {
			$query->andWhere('price<=:price_max', ['price_max' => $price_max,]);
		}
		if ($area_min !== null) {
			$query->andWhere($total_area . '>=:area_min', ['area_min' => $area_min,]);
		}
		if ($area_max !== null) {
			$query->andWhere($total_area . '<=:area_max', ['area_max' => $area_max,]);
		}
		$query->leftJoin('comrealty', 'office.comrealty_id=comrealty.id');
		if ($area !== null) {
			$ar = json_decode($area, true);
			if($ar && is_array($ar)) {
				$area = $ar;
			}
			$query->andWhere(['comrealty.area' => $area,]);
		}
		if($status !== null) {
			$statuses = explode(',', $status);
			$query->andWhere(['status' => $statuses,]);
		}
		if($city_id!==null) {
			$query->andWhere(['comrealty.city_id' => $city_id,]);
		}
		$query->select(['office.*', 'comrealty.area as comrealty_area', 'comrealty.name as comrealty_name', 'comrealty.address as comrealty_address']);
		$query->asArray();
		try {
			$provider = new ActiveDataProvider([
				'query' => $query,
				'pagination' => false
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		return $provider;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/office/comrealty/{comrealty}",
	 *     tags={"Офисы (office)"},
	 *     summary="Список офисов одного объекта недвижимости",
	 * 	   @OA\Parameter(name="comrealty", in="path", description="id коммерческой недвижимости", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * )
	 */
	public function actionComrealty($comrealty) {
		$model = new $this->modelClass;
		$query = $model->find()->where(['comrealty_id' => $comrealty,])->orderBy('id');
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
	 * @OA\Post(path="/v1/office/create",
	 * 		tags={"Офисы (office)"},
	 * 		summary="Добавление офиса",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 * 				@OA\Property(property="name", description="Название(номер)", type="string"),
	 * 				@OA\Property(property="comrealty_id", description="ID недвижимости", type="string"),
	 * 				@OA\Property(property="plan", description="json-массив координат", type="string"),
	 * 				@OA\Property(property="square", description="Площадь", type="string"),
	 * 				@OA\Property(property="price", description="Цена", type="string"),
	 * 				@OA\Property(property="status", description="Статус", type="string"),
	 * 				@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 * 				@OA\Property(property="id_1s", description="ИД 1С", type="string"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Office::create();
	}

	/**
	 * @OA\Post(path="/v1/office/updateimage/{id}",
	 * 		tags={"Офисы (office)"},
	 * 		summary="Изменение изображения",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 * 					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Office::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/office/update/{id}",
	 * 		tags={"Офисы (office)"},
	 * 		summary="Изменение офиса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Название(номер)", required=false),
	 * 		@OA\Parameter(name="comrealty_id", in="query", description="ID недвижимости", required=false),
	 * 		@OA\Parameter(name="plan", in="query", description="json-массив координат", required=false),
	 * 		@OA\Parameter(name="square", in="query", description="Площадь", required=false),
	 * 		@OA\Parameter(name="price", in="query", description="Цена", required=false),
	 * 		@OA\Parameter(name="status", in="query", description="Статус", required=false),
	 * 		@OA\Parameter(name="id_1s", in="query", description="ИД 1С", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Office::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/office/delete/{id}",
	 * 		tags={"Офисы (office)"},
	 * 		summary="Удаление офиса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * 		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Office::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/office/view/{id}",
	 *     tags={"Офисы (office)"},
	 *     summary="Просмотр офиса по id",
	 * 	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Office")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$query = Office::find();
		$query->alias('a');
		$query->where(['a.id' => $id]);
		$query->leftJoin('comrealty', 'a.comrealty_id=comrealty.id');
		$query->select(['a.*', 'comrealty.name as comrealty_name', 'comrealty.area as comrealty_area']);
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
