<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Complex;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class ComplexController extends RestController {

	public $modelClass = Complex::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'published', 'city'],
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
	 * @OA\Get(path="/v1/complex",
	 *     tags={"ЖК (complex)"},
	 *     summary="Список ЖК",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *	   security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('order, name'),
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
	 * 
	 * @OA\Get(path="/v1/complex/city/{city}",
	 *     tags={"ЖК (complex)"},
	 *     summary="Список ЖК по городу",
	 * 	   @OA\Parameter(name="city", in="path", description="Идентификатор города", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 * )
	 */
	public function actionCity($city) {
		$model = new $this->modelClass;
		$query = $model->find()->where(['city_id' => $city,])->orderBy('order, name');
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
	 * 
	 * @OA\Get(path="/v1/complex/published",
	 *     tags={"ЖК (complex)"},
	 *     summary="Список опубликованных ЖК",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 * )
	 */
	public function actionPublished() {
		$model = new $this->modelClass;
		$query = $model->find()->where(['published' => 1,])->orderBy('order, name');
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
	 * @OA\Post(path="/v1/complex/create",
	 * 		tags={"ЖК (complex)"},
	 * 		summary="Добавление ЖК",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Наименование", type="string"),
	 *		 		@OA\Property(property="city_id", description="ID города", type="string"),
	 *				@OA\Property(property="area", description="Район", type="string"),
	 *				@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *				@OA\Property(property="genplan", description="Генплан", type="string", format="binary"),
	 *		 		@OA\Property(property="order", description="Порядок", type="string"),
	 *		 		@OA\Property(property="published", description="Опубликовано", type="string"),
	 *				@OA\Property(property="images[]", description="Изображения галереи", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "City not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Complex::create(['images'=>'']);
	}

	/**
	 * @OA\Post(path="/v1/complex/updateimage/{id}",
	 * 		tags={"ЖК (complex)"},
	 * 		summary="Изменение изображения и (или) генплана",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *					@OA\Property(property="genplan", description="Генплан", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Complex::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/complex/update/{id}",
	 * 		tags={"ЖК (complex)"},
	 * 		summary="Изменение ЖК",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Наименование", required=false),
	 * 		@OA\Parameter(name="city_id", in="query", description="ID города", required=false),
	 * 		@OA\Parameter(name="area", in="query", description="Район", required=false),
	 * 		@OA\Parameter(name="order", in="query", description="Порядок", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Complex::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/complex/delete/{id}",
	 * 		tags={"ЖК (complex)"},
	 * 		summary="Удаление ЖК",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Complex::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/complex/view/{id}",
	 *     tags={"ЖК (complex)"},
	 *     summary="Просмотр ЖК по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Complex")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Complex::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['images'] = \common\models\Media::find()->where(['parent' => 'complex', 'id_parent' => $id,])->asArray()->orderBy('order, id')->all();
			$model['houses'] = \common\models\House::find()->where(['complex_id' => $id,])->asArray()->orderBy('name')->all();
			if($model['houses']) {
				foreach ($model['houses'] as $key=>$house) {
					// количество квартир со статусом 2
					$query = \common\models\Apart::find();
					$query->alias('a');
					$query->select(['count(a.id)']);
					$query->leftjoin('tplapart ta', 'a.tplapart_id=ta.id');
					$query->leftjoin('tplfloor tf', 'ta.tplfloor_id=tf.id');
					$query->leftjoin('entrance e', 'tf.entrance_id=e.id');
					$query->where(['e.house_id'=>$house['id']]);
					$query->andWhere(['a.status'=>2]);
					$model['houses'][$key]['aparts_status2'] = $query->scalar();
				}
			}
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
