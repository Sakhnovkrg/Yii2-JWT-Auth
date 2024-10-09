<?php

namespace sakhnovkrg\yii2\jwt\services;

use Yii;
use sakhnovkrg\yii2\jwt\models\UserRefreshToken;
use sakhnovkrg\yii2\jwt\JWTModule;
use sakhnovkrg\yii2\jwt\repositories\UserRefreshTokenRepository;
use yii\web\IdentityInterface;

class RefreshTokenService
{
    private JWTModule $module;
    private UserRefreshTokenRepository $userRefreshRepository;

    public function limitTokens(IdentityInterface $identity): void
    {
        $tokens = $this->userRefreshRepository->getUserTokens($identity);
        if(!count($tokens) > $this->module->maxRefreshTokensForUser) return;

        $tokensToDelete = array_slice($tokens, $this->module->maxRefreshTokensForUser - 1);

        foreach ($tokensToDelete as $token) {
            $this->userRefreshRepository->delete($token);
        }
    }

    public function logout(IdentityInterface $identity): void
    {
        Yii::$app->response->cookies->remove('refresh_token');
    }

    public function validateRefreshToken($token): IdentityInterface|bool
    {
        $model = $this->userRefreshRepository->findByToken($token);
        $identity = (new Yii::$app->user->identityClass)->findIdentity($model->user_id);
        if(!$identity) return false;
        $this->userRefreshRepository->delete($model);
        Yii::$app->user->login($identity);
        return $identity;
    }

    public function generateRefreshToken(IdentityInterface $identity): UserRefreshToken
    {
        return $this->userRefreshRepository->create(
            $identity->getId(),
            Yii::$app->security->generateRandomString()
        );
    }

    public function __construct(UserRefreshTokenRepository $repository)
    {
        $this->module = Yii::$app->getModule('jwt-auth');
        $this->userRefreshRepository = $repository;
    }
}
