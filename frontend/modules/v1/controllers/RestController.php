<?php

namespace frontend\modules\v1\controllers;

use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\Response;
use Yii;

class RestController extends ActiveController {

	public $enableCsrfValidation = false;
	public $serializer = [
		'class' => 'yii\rest\Serializer',
		'collectionEnvelope' => 'items',
	];

	public function actions() {
		$actions = parent::actions();
		$actions['create']['class'] = 'frontend\modules\v1\rest\CreateAction';
		$actions['delete']['class'] = 'frontend\modules\v1\rest\DeleteAction';
		$actions['update']['class'] = 'frontend\modules\v1\rest\UpdateStudentAction';
		return $actions;
	}

	public function verbs() {
		return [
			'index' => ['GET', 'HEAD'],
			'published' => ['GET', 'HEAD'],
			'view' => ['GET', 'HEAD'],
			'create' => ['POST'],
			'update' => ['PUT', 'PATCH'],
			'delete' => ['DELETE'],
			'options' => ['OPTIONS'],
			'upload-excel' => ['POST'],
		];
	}

	public function behaviors() {
		$behaviors = parent::behaviors();
		$behaviors['corsFilter'] = [
			'class' => Cors::class,
		];
		$behaviors['contentNegotiator'] = [
			'class' => ContentNegotiator::class,
			'formatParam' => '_format',
			'formats' => ['application/json' => Response::FORMAT_JSON]
		];
/*		$behaviors['rateLimiter'] = [
			// Use class
			'class' => \ethercreative\ratelimiter\RateLimiter::className(),
			// The maximum number of allowed requests
			'rateLimit' => 1000000,
			// The time period for the rates to apply to
			'timePeriod' => 600,
			// Separate rate limiting for guests and authenticated users
			// Defaults to true
			// - false: use one set of rates, whether you are authenticated or not
			// - true: use separate ratesfor guests and authenticated users
			'separateRates' => false,
			// Whether to return HTTP headers containing the current rate limiting information
			'enableRateLimitHeaders' => false,
		]; */
		return $behaviors;
	}

}
