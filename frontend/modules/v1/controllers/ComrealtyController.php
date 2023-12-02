<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Comrealty;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ComrealtyController extends RestController {

	public $modelClass = Comrealty::class;

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
	 * @OA\Get(path="/v1/comrealty",
	 *     tags={"Коммерческая недвижимость (comrealty)"},
	 *     summary="Список коммерческой недвижимости",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('city_id, name'),
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
	 * @OA\Post(path="/v1/comrealty/create",
	 * 		tags={"Коммерческая недвижимость (comrealty)"},
	 * 		summary="Добавление коммерческой недвижимости",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Название превью", type="string"),
	 *				@OA\Property(property="name_search", description="Название для результатов поиска", type="string"),
	 *				@OA\Property(property="address", description="Адрес в результатах поиска", type="string"),
	 *				@OA\Property(property="area", description="Район", type="string"),
	 *				@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *				@OA\Property(property="imageplan", description="Схема", type="string", format="binary"),
	 *		 		@OA\Property(property="city_id", description="ID города", type="string"),
	 *				@OA\Property(property="coord", description="Координаты на карте", type="string"),
	 *				@OA\Property(property="house_id", description="ID дома", type="string"),
	 *				@OA\Property(property="description", description="Описание", type="string"),
	 *				@OA\Property(property="images[]", description="Изображения галереи", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "City not found",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Comrealty::create(['images'=>'']);
	}

	/**
	 * @OA\Post(path="/v1/comrealty/updateimage/{id}",
	 * 		tags={"Коммерческая недвижимость (comrealty)"},
	 * 		summary="Изменение изображения",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *					@OA\Property(property="imageplan", description="Схема", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Comrealty::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/comrealty/update/{id}",
	 * 		tags={"Коммерческая недвижимость (comrealty)"},
	 * 		summary="Изменение коммерческой недвижимости",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Название превью", required=false),
	 * 		@OA\Parameter(name="name_search", in="query", description="Название для результатов поиска", required=false),
	 * 		@OA\Parameter(name="address", in="query", description="Адрес в результатах поиска", required=false),
	 * 		@OA\Parameter(name="area", in="query", description="Район", required=false),
	 * 		@OA\Parameter(name="city_id", in="query", description="ID города", required=false),
	 * 		@OA\Parameter(name="coord", in="query", description="Координаты на карте", required=false),
	 * 		@OA\Parameter(name="house_id", in="query", description="ID дома", required=false),
	 * 		@OA\Parameter(name="description", in="query", description="Описание", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Comrealty::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		if($model->upd()) {
			return $model;
		}
		return false;
	}

	/**
	 * @OA\Delete(path="/v1/comrealty/delete/{id}",
	 * 		tags={"Коммерческая недвижимость (comrealty)"},
	 * 		summary="Удаление коммерческой недвижимости",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Comrealty::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/comrealty/view/{id}",
	 *     tags={"Коммерческая недвижимость (comrealty)"},
	 *     summary="Просмотр по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Comrealty")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Comrealty::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['images'] = \common\models\Media::find()->where(['parent' => 'comrealty', 'id_parent' => $id,])->asArray()->orderBy('order, id')->all();
			$model['offices'] = \common\models\Office::find()->where(['comrealty_id' => $id,])->asArray()->orderBy('name')->all();
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
