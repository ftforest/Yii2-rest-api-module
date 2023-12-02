<?php

namespace frontend\modules\v1\controllers;

use Yii;
use frontend\modules\v1\models\Quest;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class QuestController extends RestController {

	public $modelClass = Quest::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'view', 'index', 'themes', 'create'],
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
	 * @OA\Get(path="/v1/quest",
	 *     tags={"Вопросы-ответы (quest)"},
	 *     summary="Список вопросов",
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=false),
	 * 	   @OA\Parameter(name="theme", in="query", description="Тема (полностью или фрагмент)", required=false),
	 *	   @OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 * )
	 */
	public function actionIndex($city_id = null, $published = null, $theme = null) {
		$model = new $this->modelClass;
		$query = $model->find()->orderBy('city_id, name');
		if($city_id !== null) {
			$query->where(['city_id' => $city_id,]);
		}
		if($published !== null) {
			$query->where(['published' => $published,]);
		}
		if($theme) {
			$query->where(['like', 'theme', $theme]);
		}
		try {
			$provider = new ActiveDataProvider([
				'query' => $query,
					//'pagination' => false
			]);
		} catch (\Exception $ex) {
			throw new \yii\web\HttpException(500, 'Internal server error');
		}

		return $provider;
	}

	/**
	 * 
	 * @OA\Get(path="/v1/quest/themes",
	 *     tags={"Вопросы-ответы (quest)"},
	 *     summary="Список тем",
	 * 	   @OA\Parameter(name="city_id", in="query", description="ИД города", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK"
	 *     ),
	 * )
	 */
	public function actionThemes($city_id = null) {
		return Quest::getThemes($city_id);
	}

	/**
	 * @OA\Post(path="/v1/quest/create",
	 * 		tags={"Вопросы-ответы (quest)"},
	 * 		summary="Добавление вопроса",
	 *     @OA\RequestBody(
	 *         @OA\MediaType(
	 *             mediaType="multipart/form-data",
	 *             @OA\Schema(
	 *				@OA\Property(property="name", description="Имя автора", type="string"),
	 *				@OA\Property(property="email", description="Email автора", type="string"),
	 *				@OA\Property(property="theme", description="Тема", type="string"),
	 *				@OA\Property(property="quest", description="Вопрос", type="string"),
	 *				@OA\Property(property="answer", description="Ответ", type="string"),
	 *				@OA\Property(property="city_id", description="ID города", type="string"),
	 *				@OA\Property(property="published", description="Опубликовано", type="string"),
	 *				@OA\Property(property="images[]", description="Изображения", type="string", format="binary"),
	 *             ),
	 *         ),
	 *     ),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "City not found",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Quest::create(['images'=>'']);
	}

	/**
	 * @OA\Put(path="/v1/quest/update/{id}",
	 * 		tags={"Вопросы-ответы (quest)"},
	 * 		summary="Изменение вопроса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Имя автора", required=false),
	 * 		@OA\Parameter(name="email", in="query", description="Email автора", required=false),
	 * 		@OA\Parameter(name="theme", in="query", description="Тема", required=false),
	 * 		@OA\Parameter(name="quest", in="query", description="Вопрос", required=false),
	 * 		@OA\Parameter(name="answer", in="query", description="Ответ", required=false),
	 * 		@OA\Parameter(name="house_id", in="query", description="ID дома", required=false),
	 * 		@OA\Parameter(name="city_id", in="query", description="ID города", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Parameter(name="emailsended", in="query", description="Сообщение отправлено", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 * )
	 */
	public function actionUpdate($id) {
		$model = Quest::find()->where(['id' => $id,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/quest/delete/{id}",
	 * 		tags={"Вопросы-ответы (quest)"},
	 * 		summary="Удаление вопроса",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = Quest::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/quest/view/{id}",
	 *     tags={"Вопросы-ответы (quest)"},
	 *     summary="Просмотр вопроса по id",
	 *	   @OA\Parameter(name="id", in="path", description="Ид", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Quest")
	 *     ),
	 * )
	 */
	public function actionView($id) {
		$model = Quest::find()->where(['id' => $id])->asArray()->one();

		if ($model) {
			$model['images'] = \common\models\Media::find()->where(['parent' => 'quest', 'id_parent' => $id,])->asArray()->orderBy('order, id')->all();
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
		if ($action === 'delete' || $action === 'update') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
