<?php

namespace frontend\modules\v1\rest;

use Yii;

class CreateAction extends \yii\rest\CreateAction
{
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        $model->scenario = $model::SCENARIO_CREATE;
        $model->attributes = \Yii::$app->request->post();

        if($model->validate()){
            $model->save();
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
            return ['status' => true, 'model' => $model];
        }

        if($model->errors){
            Yii::$app->getResponse()->setStatusCode(400);
            return ['status' => false, 'message' => $model->getErrors()];
        }

        Yii::$app->getResponse()->setStatusCode(400);
        return ['status' => false, 'message' => 'Failed to create the object for unknown reason.'];
    }
}