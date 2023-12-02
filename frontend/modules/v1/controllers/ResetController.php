<?php

namespace frontend\modules\v1\controllers;

use frontend\modules\v1\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;

class ResetController extends RestController
{
    public $modelClass = PasswordResetRequestForm::class;

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    /**
     * @_SWG\Post(path="/v1/reset",
     *     tags={"reset"},
     *     summary="Восстановление пароля",
     *     @_SWG\Parameter(
     *         name="email",
     *         in="path",
     *         description="Почта",
     *         required=false,
     *         type="string",
     *     ),
     *     @_SWG\Response(
     *         response = 200,
     *         description = "Задать новый пароль",
     *     ),
     * )
     */
    public function actionCreate()
    {
        $model = new PasswordResetRequestForm();
        $model->attributes = Yii::$app->request->post();

        if ($model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getResponse()->setStatusCode(200);

                return ['status' => true, 'message' => 'Дальнейшие инструкции отправлены на E-Mail'];
            }

            Yii::$app->getResponse()->setStatusCode(400);
            return ['status' => false, 'message' => 'Мы не можем сбросить пароль для данной почты.'];
        }

        Yii::$app->getResponse()->setStatusCode(400);
        return $model->getErrors();
    }

    public function actionIndex()
    {
        return '';
    }

    /**
     * @_SWG\Post(path="/v1/reset/new",
     *     tags={"reset"},
     *     summary="Задать новый пароль",
     *     @_SWG\Parameter(
     *         name="token",
     *         in="path",
     *         description="Token из письма",
     *         required=false,
     *         type="string",
     *     ),
     *     @_SWG\Parameter(
     *         name="password",
     *         in="path",
     *         description="Новый пароль",
     *         required=false,
     *         type="string",
     *     ),
     *     @_SWG\Response(
     *         response = 200,
     *         description = "Задать новый пароль",
     *     ),
     * )
     */
    public function actionNew()
    {
        $post = Yii::$app->request->post();
        try {
            $model = new ResetPasswordForm($post['token']);
            $model->attributes = Yii::$app->request->post();
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->validate() && $model->resetPassword()) {
            return ['status' => true, 'message' => 'Новый пароль сохранен'];
        }

        Yii::$app->getResponse()->setStatusCode(400);
        return $model->getErrors();
    }
}