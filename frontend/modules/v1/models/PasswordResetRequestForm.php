<?php

namespace frontend\modules\v1\models;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @_SWG\Definition(required={"email"})
 *
 * @_SWG\Property(property="email", type="string")
 */

class PasswordResetRequestForm extends \frontend\models\PasswordResetRequestForm
{
    const SCENARIO_CREATE = 'create';

    public function rules()
    {
        $rules = parent::rules();
        $rules[] = [['email'], 'required', 'on' => self::SCENARIO_CREATE];
        return $rules;
    }

    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            self::SCENARIO_CREATE => ['email']
        ]);
    }

    public function extraFields()
    {
        return [
            ''
        ];
    }
}