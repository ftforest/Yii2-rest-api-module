<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Tplfloor;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class TplfloorController extends RestController {

	public $modelClass = Tplfloor::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'entrance'],
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
     * {@inheritdoc}
     */
	public function verbs() {
		$ret = parent::verbs();
		$ret['entrance'] = ['GET', 'HEAD'];
		return $ret;
	}

	
	/**
	 * 
	 * @OA\Get(path="/v1/tplfloor",
	 *     tags={"Шаблоны этажей (tplfloor)"},
	 *     summary="Список шаблонов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('entrance_id, id'),
					//'pagination' => false
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		return $provider;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/tplfloor/entrance/{entrance}",
	 *     tags={"Шаблоны этажей (tplfloor)"},
	 *     summary="Список шаблонов подъезда",
	 * 	   @OA\Parameter(name="entrance", in="path", description="ID подъезда", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 * )
	 */
	public function actionEntrance($entrance) {
		$model = new $this->modelClass;
		$query = $model->find()->where(['entrance_id' => $entrance,])->orderBy('name');
		try {
			$provider = new ActiveDataProvider([
				'query' => $query,
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
	 * @OA\Post(path="/v1/tplfloor/create",
	 * 		tags={"Шаблоны этажей (tplfloor)"},
	 * 		summary="Добавление шаблона",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="name", description="Название (этаж или диапазон этажей)", type="string"),
	 *					@OA\Property(property="image", description="Изображение (схема)", type="string", format="binary"),
	 *					@OA\Property(property="street1", description="Улица слева", type="string"),
	 *					@OA\Property(property="street2", description="Улица сверху", type="string"),
	 *					@OA\Property(property="street3", description="Улица справа", type="string"),
	 *					@OA\Property(property="street4", description="Улица снизу", type="string"),
	 *					@OA\Property(property="compas", description="Компас", type="string"),
	 *					@OA\Property(property="entrance_id", description="ИД подъезда", type="string"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Tplfloor::create();
	}

	/**
	 * @OA\Post(path="/v1/tplfloor/updateimage/{id}",
	 * 		tags={"Шаблоны этажей (tplfloor)"},
	 * 		summary="Изменение изображения шаблона",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *					@OA\Property(property="image", description="Изображение", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdateimage($id) {
		$model = Tplfloor::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->updimage();
	}

	/**
	 * @OA\Put(path="/v1/tplfloor/update/{id}",
	 * 		tags={"Шаблоны этажей (tplfloor)"},
	 * 		summary="Изменение шаблона",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Наименование", required=false),
	 * 		@OA\Parameter(name="street1", in="query", description="Улица слева", required=false),
	 * 		@OA\Parameter(name="street2", in="query", description="Улица сверху", required=false),
	 * 		@OA\Parameter(name="street3", in="query", description="Улица справа", required=false),
	 * 		@OA\Parameter(name="street4", in="query", description="Улица снизу", required=false),
	 * 		@OA\Parameter(name="compas", in="query", description="Компас", required=false),
	 * 		@OA\Parameter(name="entrance_id", in="query", description="entrance_identrance_id", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Tplfloor::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/tplfloor/delete/{id}",
	 * 		tags={"Шаблоны этажей (tplfloor)"},
	 * 		summary="Удаление шаблона",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Tplfloor::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/tplfloor/view/{id}",
	 *     tags={"Шаблоны этажей (tplfloor)"},
	 *     summary="Просмотр шаблона этажей по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Tplfloor")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Tplfloor::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['tplaparts'] = \common\models\Tplapart::find()->where(['tplfloor_id' => $id,])->asArray()->orderBy('name')->all();
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
