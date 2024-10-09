<?php

namespace sakhnovkrg\yii2\jwt\repositories;

use Yii;
use sakhnovkrg\yii2\jwt\models\UserRefreshToken;
use yii\web\IdentityInterface;

class UserRefreshTokenRepository
{
    /**
     * @param int $userId
     * @param string $token
     * @return UserRefreshToken
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\Exception
     * @throws \yii\di\NotInstantiableException
     */
    public function create(
        int $userId,
        string $token,
    ): UserRefreshToken {
        $model = Yii::$container->get(UserRefreshToken::class);
        $model->user_id = $userId;
        $model->token = $token;
        $model->ip = Yii::$app->request->userIP;
        $model->user_agent = Yii::$app->request->userAgent;
        $model->created_at = gmdate('Y-m-d H:i:s');

        if (!$model->save()) {
            throw new \RuntimeException('Unable to create refresh token.');
        }

        return $model;
    }

    public function findByToken(string $token): UserRefreshToken
    {
        $model = Yii::$container->get(UserRefreshToken::class);

        $result = $model::findOne(['token' => $token]);

        if (!$result) {
            throw new \RuntimeException('Token not found.');
        }

        return $result;
    }

    /**
     * @param IdentityInterface $identity
     * @return UserRefreshToken[]
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function getUserTokens(IdentityInterface $identity): array
    {
        $model = Yii::$container->get(UserRefreshToken::class);
        return $model::find()->where(['user_id' => $identity->getId()])->orderBy(['id' => SORT_DESC])->all();
    }

    public function delete(UserRefreshToken $token): bool
    {
        return $token->delete();
    }
}
