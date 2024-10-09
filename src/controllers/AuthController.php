<?php

namespace sakhnovkrg\yii2\jwt\controllers;

use Yii;
use sakhnovkrg\yii2\jwt\services\RefreshTokenService;
use sakhnovkrg\yii2\jwt\filters\JWTAuthenticator;
use sakhnovkrg\yii2\jwt\models\AbstractLoginForm;
use sakhnovkrg\yii2\jwt\services\JWTService;
use yii\filters\ContentNegotiator;
use yii\helpers\Json;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;

class AuthController extends Controller
{
    private JWTService $jwtService;
    private RefreshTokenService $refreshTokenService;

    public function behaviors()
    {
        return [
            [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            [
                'class' => JWTAuthenticator::class,
                'except' => ['login', 'refresh']
            ]
        ];
    }

    public function __construct($id, $module, JWTService $jwtService, RefreshTokenService $refreshTokenService)
    {
        $this->jwtService = $jwtService;
        $this->refreshTokenService = $refreshTokenService;
        parent::__construct($id, $module);
    }

    public function actionLogin(): array
    {
        try {
            $bodyParams = Json::decode(Yii::$app->request->getRawBody());
        } catch (\Exception) {
            throw new BadRequestHttpException('Request body must be an valid JSON.');
        }

        $model = Yii::$container->get(AbstractLoginForm::class);
        $model->setAttributes($bodyParams);
        if (!$model->validate()) {
            throw new BadRequestHttpException(array_values($model->getFirstErrors())[0]);
        }

        return $this->jwtService->generateTokensResponse($model->getIdentity());
    }

    public function actionRefresh()
    {
        $refreshToken = Yii::$app->request->cookies->getValue('refresh_token');

        if (!$refreshToken) {
            throw new BadRequestHttpException('Refresh token is required.');
        }

        if(!$this->refreshTokenService->validateRefreshToken($refreshToken)) {
            throw new BadRequestHttpException('Invalid refresh token.');
        }

        return $this->jwtService->generateTokensResponse(Yii::$app->user->identity);
    }

    public function actionLogout()
    {
        $this->refreshTokenService->logout(Yii::$app->user->getIdentity());
        return $this->asJson(['message' => 'Successfully logged out']);
    }

    public function actionMe(): IdentityInterface
    {
        return Yii::$app->user->getIdentity();
    }
}
