<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\Page;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class PageController extends RestController {

	public $modelClass = Page::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options', 'short', 'index'],
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
	 * @OA\Get(path="/v1/page",
	 *     tags={"Страницы (page)"},
	 *     summary="Список страниц",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "Список страниц",
	 *         @OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 * )
	 */
	public function actionIndex() {
		$model = new $this->modelClass;
		try {
			$provider = new ActiveDataProvider([
				'query' => $model->find(),
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
	 * @OA\Post(path="/v1/page/create",
	 * 		tags={"Страницы (page)"},
	 * 		summary="Добавление страницы",
	 * 		@OA\Parameter(name="name", in="query", description="Наименование", required=true),
	 * 		@OA\Parameter(name="short", in="query", description="Назначение", required=true),
	 * 		@OA\Parameter(name="content", in="query", description="Содержание", required=true),
	 * 		@OA\Parameter(name="title", in="query", description="Meta Title", required=false),
	 * 		@OA\Parameter(name="meta_description", in="query", description="Meta Description", required=false),
	 * 		@OA\Parameter(name="meta_keywords", in="query", description="Meta Keywords", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Страница добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return Page::create();
	}

	/**
	 * @OA\Put(path="/v1/page/update/{short}",
	 * 		tags={"Страницы (page)"},
	 * 		summary="Изменение страницы",
	 * 		@OA\Parameter(name="short", in="path", description="Назначение", required=true),
	 * 		@OA\Parameter(name="name", in="query", description="Наименование", required=false),
	 * 		@OA\Parameter(name="content", in="query", description="Содержание", required=false),
	 * 		@OA\Parameter(name="title", in="query", description="Meta Title", required=false),
	 * 		@OA\Parameter(name="meta_description", in="query", description="Meta Description", required=false),
	 * 		@OA\Parameter(name="meta_keywords", in="query", description="Meta Keywords", required=false),
	 * 		@OA\Parameter(name="published", in="query", description="Опубликовано", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Страница обновлена",
	 * 			@OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionUpdate($short) {
		$model = Page::find()->where(['short' => $short,])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		return $model->upd();
	}

	/**
	 * @OA\Delete(path="/v1/page/delete/{short}",
	 * 		tags={"Страницы (page)"},
	 * 		summary="Удаление страницы. Вместо short можно использовать id",
	 * 		@OA\Parameter(name="short", in="path", description="Назначение", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Страница удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($short) {
		$model = Page::find()->where(['short' => $short,])->one();
		if(!$model && is_numeric($short)) {
			$model = Page::find()->where(['id' => $short])->one();
		}
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$id = $model->id;
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/page/{short}",
	 *     tags={"Страницы (page)"},
	 *     summary="Просмотр страницы по назначению",
	 *     @OA\Parameter(name="short", in="path", description="Назначение", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/Page")
	 *     ),
	 * )
	 */
	public function actionShort($short) {
		$model = Page::find()->where(['short' => $short])->one();

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
