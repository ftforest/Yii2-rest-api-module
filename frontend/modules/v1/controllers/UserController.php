<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\User;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;
use yii\filters\auth\HttpBearerAuth;

class UserController extends RestController {

	public $modelClass = User::class;

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => HttpBearerAuth::class,
			'except' => ['options'],
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
	 * @OA\Get(path="/v1/user",
	 *     tags={"Пользователи (user)"},
	 *     summary="Список пользователей",
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "Список пользователей",
	 *         @OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *		security={{"bearerAuth":{}}}
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
	 * @OA\Post(path="/v1/user/create",
	 * 		tags={"Пользователи (user)"},
	 * 		summary="Добавление пользователя",
	 *		@OA\Parameter(name="email", in="query", description="Email", required=true),
	 *		@OA\Parameter(name="password", in="query", description="Пароль", required=true),
	 *		@OA\Parameter(name="username", in="query", description="Имя", required=true),
	 *		@OA\Parameter(name="surname", in="query", description="Фамилия", required=false),
	 *		@OA\Parameter(name="phone", in="query", description="Телефон", required=false),
	 *		@OA\Parameter(name="type", in="query", description="Админ (hirer) или пользователь (other)", required=false),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись добавлена",
	 * 			@OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionCreate() {
		return User::create();
	}
	
	/**
	 * @OA\Delete(path="/v1/user/delete/{id}",
	 * 		tags={"Пользователи (user)"},
	 * 		summary="Удаление пользователя",
	 * 		@OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 * 		@OA\Response(
	 * 			response = 200,
	 * 			description = "Запись удалена",
	 * 			@OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionDelete($id) {
		$model = User::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new \yii\web\HttpException(404, 'No entries found with this query string');
		}
		$model->delete();
		return ['id' => $id,];
	}

	/**
	 *
	 * @OA\Get(path="/v1/user/{id}",
	 *     tags={"Пользователи (user)"},
	 *     summary="Просмотр пользователя",
	 *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
	 *     @OA\Response(
	 *         response = 200,
	 * 		   description = "OK",
	 *         @OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *     @OA\Response(
	 *         response = 404,
	 * 		   description = "Not found",
	 *         @OA\Schema(ref = "#/components/schemas/User")
	 *     ),
	 *		security={{"bearerAuth":{}}}
	 * )
	 */
	public function actionView($id) {
		$model = User::find()->where(['id' => $id])->one();

		if ($model) {
			return $model;
		}
		throw new \yii\web\HttpException(404, 'No entries found with this query string');
	}

    /**
     *
     * @OA\Put(path="/v1/user/{id}",
     *     tags={"Пользователи (user)"},
     *     summary="Редактирование пользователя",
     *	   @OA\Parameter(name="id", in="path", description="Идентификатор", required=true),
     *     @OA\Response(
     *         response = 200,
     * 		   description = "OK",
     *         @OA\Schema(ref = "#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response = 404,
     * 		   description = "Not found",
     *         @OA\Schema(ref = "#/components/schemas/User")
     *     ),
     *		security={{"bearerAuth":{}}}
     * )
     */
    public function actionUpdate($id) {
        $model = User::find()->where(['id' => $id])->one();

        if (!$model) {
            throw new \yii\web\HttpException(404, 'No entries found with this query string');
        }

        $model->scenario = User::SCENARIO_UPDATE;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->save()) {
            Yii::$app->response->setStatusCode(200);
            return [
                'message' => 'User updated successfully',
                'data' => $model,
            ];
        } else {
            Yii::$app->response->setStatusCode(400);
            return [
                'error' => 'Failed to update user',
                'errors' => $model->errors,
            ];
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
		if ($action === 'create' || $action === 'delete'  || $action === 'update' || $action === 'view' || $action === 'index') {
			if (\Yii::$app->user->isGuest) {
				throw new ForbiddenHttpException("Authorization required");
			}
			if (!\Yii::$app->user->can('admin')) {
				throw new ForbiddenHttpException("You don't have permission: admin");
			}
		}
	}

}
