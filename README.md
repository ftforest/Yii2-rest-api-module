<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii 2 Advanced Project Template</h1>
    <br>
</p>

Yii 2 Advanced Project Template is a skeleton [Yii 2](https://www.yiiframework.com/) application best for
developing complex Web applications with multiple tiers.

The template includes three tiers: front end, back end, and console, each of which
is a separate Yii application.

The template is designed to work in a team development environment. It supports
deploying the application in different environments.

Documentation is at [docs/guide/README.md](docs/guide/README.md).

[![Latest Stable Version](https://img.shields.io/packagist/v/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![Total Downloads](https://img.shields.io/packagist/dt/yiisoft/yii2-app-advanced.svg)](https://packagist.org/packages/yiisoft/yii2-app-advanced)
[![build](https://github.com/yiisoft/yii2-app-advanced/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-app-advanced/actions?query=workflow%3Abuild)

DIRECTORY STRUCTURE
-------------------

```
common
    config/              contains shared configurations
    mail/                contains view files for e-mails
    models/              contains model classes used in both backend and frontend
    tests/               contains tests for common classes    
console
    config/              contains console configurations
    controllers/         contains console controllers (commands)
    migrations/          contains database migrations
    models/              contains console-specific model classes
    runtime/             contains files generated during runtime
backend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains backend configurations
    controllers/         contains Web controller classes
    models/              contains backend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for backend application    
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
frontend
    assets/              contains application assets such as JavaScript and CSS
    config/              contains frontend configurations
    controllers/         contains Web controller classes
    models/              contains frontend-specific model classes
    runtime/             contains files generated during runtime
    tests/               contains tests for frontend application
    views/               contains view files for the Web application
    web/                 contains the entry script and Web resources
    widgets/             contains frontend widgets
vendor/                  contains dependent 3rd-party packages
environments/            contains environment-based overrides
```
### Вопросы

[frontend/modules/v1/models/User.php](frontend/modules/v1/models/User.php)

$user->patronymic = '';

$user->temporary_pass = '';

### То что сделал

[localhost/frontend/config/main.php](localhost/frontend/config/main.php)
```php
    'modules' => [
        'v1' => [
            'class' => 'frontend\modules\v1\Module',
        ],
    ],
    'components' => [
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['site'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/user'],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['v1/auth'],
                ],
            ],
        ],
        'request' => [
            'csrfParam' => '_csrf-frontend',
            /*'parsers' => [
                'application/json' => 'yiiwebJsonParser',
            ]*/
        ],
```

[frontend/modules/v1/controllers/RestController.php](frontend/modules/v1/controllers/RestController.php)

```php
        public function actions() {
		$actions = parent::actions();
		$actions['create']['class'] = 'frontend\modules\v1\rest\CreateAction';
		$actions['delete']['class'] = 'frontend\modules\v1\rest\DeleteAction';
		// добавлено обновление пользователя
		$actions['update']['class'] = 'frontend\modules\v1\rest\UpdateStudentAction';
		return $actions;
	}

```

[frontend/modules/v1/controllers/UserController.php](frontend/modules/v1/controllers/UserController.php)

```php
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
```

[common/models/User.php](common/models/User.php)

```php
    public $patronymic;
    public $temporary_pass;

    const SCENARIO_AUTHORIZATION = 'authorization';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CREATE = 'create';
    ...
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }
    ...
    /**
     * Finds user by email
     *
     * @param string $email
     * @return static|null
     */
    public static function findByEmail($email)
    {
        return static::findOne(['email' => $email, 'status' => self::STATUS_ACTIVE]);
    }
    ...
    public function generateAccessToken($expireInSeconds = 86400)
    {
        $this->access_token = Yii::$app->security->generateRandomString() . '_' . (time() + $expireInSeconds);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_AUTHORIZATION] = ['email', 'password']; // поля, которые должны валидироваться при сценарии 'authorization'
        $scenarios[self::SCENARIO_UPDATE] = ['email', 'password', 'username', 'surname', 'status', 'phone']; // поля, которые должны валидироваться при сценарии обновления данных пользователя
        $scenarios[self::SCENARIO_CREATE] = ['username', 'email', 'password']; // поля, которые должны валидироваться при сценарии создания пользователя
        return $scenarios;
    }

```