<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Entrance;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class EntranceController extends RestController {

	public $modelClass = Entrance::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'house'],
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
		$ret['house'] = ['GET', 'HEAD'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/entrance",
	 *     tags={"Подъезды (Entrance)"},
	 *     summary="Список подъездов",
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
	 *	   security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('house_id,id'),
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
	 * @OA\Get(path="/v1/entrance/house/{house}",
	 *     tags={"Подъезды (Entrance)"},
	 *     summary="Список подъездов дома",
	 * 	   @OA\Parameter(name="house", in="path", description="ID дома", required=true),
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
	public function actionHouse($house) {
		$model = new $this->modelClass;
		$query = $model->find()->where(['house_id' => $house,])->orderBy('name');
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
	 * @OA\Post(path="/v1/entrance/create",
	 * 		tags={"Подъезды (Entrance)"},
	 * 		summary="Добавление подъезда",
	 *		@OA\Parameter(name="name", in="query", description="Название(номер)", required=true),
	 *		@OA\Parameter(name="house_id", in="query", description="ID дома", required=true),
	 *		@OA\Parameter(name="floors", in="query", description="Всего этажей", required=false),
	 *		@OA\Parameter(name="plan", in="query", description="json-массив координат", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Entrance::create();
	}

	/**
	 * @OA\Put(path="/v1/entrance/update/{id}",
	 * 		tags={"Подъезды (Entrance)"},
	 * 		summary="Изменение подъезда",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Название(номер)", required=false),
	 *		@OA\Parameter(name="house_id", in="query", description="ID дома", required=false),
	 *		@OA\Parameter(name="floors", in="query", description="Всего этажей", required=false),
	 *		@OA\Parameter(name="plan", in="query", description="json-массив координат", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Entrance::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/entrance/delete/{id}",
	 * 		tags={"Подъезды (Entrance)"},
	 * 		summary="Удаление подъезда",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Entrance")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Entrance::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/entrance/view/{id}",
	 *     tags={"Подъезды (Entrance)"},
	 *     summary="Просмотр подъезда по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
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
	public function actionView($id) {
		$model = Entrance::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['tplfloors'] = \common\models\Tplfloor::find()->where(['entrance_id' => $id,])->asArray()->orderBy('name')->all();
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
