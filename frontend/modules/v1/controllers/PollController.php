<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Poll;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class PollController extends RestController {

	public $modelClass = Poll::class;

    /**
     * {@inheritdoc}
     */
	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'published', 'vote', 'answers', 'index'],
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
		$ret['answers'] = ['GET', 'HEAD'];
		$ret['status'] = ['PUT', 'PATCH'];
		$ret['vote'] = ['PUT', 'PATCH'];
		$ret['createanswer'] = ['POST'];
		$ret['deleteanswer'] = ['DELETE'];
		return $ret;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/poll",
	 *     tags={"Опросы (Poll)"},
	 *     summary="Список опросов",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find()->orderBy('id'),
				'pagination' => false
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		return $provider;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/poll/answers/{id}",
	 *     tags={"Опросы (Poll)"},
	 *     summary="Список ответов опроса",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 * )
	 */
	public function actionAnswers($id) {
		$query = \common\models\Answer::find()->orderBy('id');
		$query->where(['poll_id' => $id,]);
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
	 * @OA\Get(path="/v1/poll/published",
	 *     tags={"Опросы (Poll)"},
	 *     summary="Опубликованный опрос",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 * )
	 */
	public function actionPublished() {
		$model = Poll::find()->where(['published' => 1])->asArray()->one();

		if ($model) {
			$model['answers'] = \common\models\Answer::findAll(['poll_id' => $model['id'],]);
			return $model;
		}
		throw new \yii\web\HttpException(404, 'No entries found with this query string');
	}

	/**
	 * 
	 * @OA\Put(path="/v1/poll/status/{id}",
	 *     tags={"Опросы (Poll)"},
	 *     summary="Изменить статус опроса",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *	   @OA\Parameter(name="publish", in="query", description="Опубликовано (1-да, 0-нет)", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionStatus($id, $publish) {
		$model = Poll::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->setStatus($publish);
	}

	/**
	 * 
	 * @OA\Put(path="/v1/poll/vote/{id}",
	 *     tags={"Опросы (Poll)"},
	 *     summary="Добавить голос ответу опроса",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 * )
	 */
	public function actionVote($id) {
		$model = \common\models\Answer::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->voices++;
		return $model->save();
	}

	/**
	 * @OA\Post(path="/v1/poll/create",
	 * 		tags={"Опросы (Poll)"},
	 * 		summary="Добавление опроса",
	 *		@OA\Parameter(name="name", in="query", description="Наименование", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Poll::create();
	}

	/**
	 * @OA\Post(path="/v1/poll/createanswer/{id}",
	 * 		tags={"Опросы (Poll)"},
	 * 		summary="Добавление ответа опросу",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Наименование", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreateanswer($id, $name) {
		$model = Poll::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->createanswer($name);
	}

	/**
	 * @OA\Put(path="/v1/poll/update/{id}",
	 * 		tags={"Опросы (Poll)"},
	 * 		summary="Изменение опроса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *		@OA\Parameter(name="name", in="query", description="Наименование", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($id) {
		$model = Poll::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/poll/delete/{id}",
	 * 		tags={"Опросы (Poll)"},
	 * 		summary="Удаление опроса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found"
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Poll::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 * @OA\Delete(path="/v1/poll/deleteanswer/{id}",
	 * 		tags={"Опросы (Poll)"},
	 * 		summary="Удаление ответа опроса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена"
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found"
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDeleteanswer($id) {
		$model = \common\models\Answer::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/poll/view/{id}",
	 *     tags={"Опросы (Poll)"},
	 *     summary="Просмотр опроса по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Poll")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Poll::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['answers'] = \common\models\Answer::findAll(['poll_id' => $id,]);
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
		if ($action === 'create' || $action === 'delete' || $action === 'deleteanswer' || $action === 'update' || $action === 'createanswer' || $action === 'status') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
