<?php

namespace sakhnovkrg\yii2\jwt\traits;

use sakhnovkrg\yii2\jwt\services\JWTService;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use yii\web\UnauthorizedHttpException;

trait JWTAuthTrait
{
    public static function findIdentityByJWTToken($token)
    {
        $jwtService = \Yii::$container->get(JWTService::class);

        try {
            $decodedToken = JWT::decode($token, new Key($jwtService->getJWTSecret(), 'HS256'));
        } catch (\Exception $e) {
            throw new UnauthorizedHttpException($e->getMessage());
        }

        return call_user_func([\Yii::$app->user->identityClass, 'findIdentity'], $decodedToken->id);
    }
}
