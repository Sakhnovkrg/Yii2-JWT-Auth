<?php

namespace sakhnovkrg\yii2\jwt\models;

/**
 * This is the ActiveQuery class for [[UserRefreshToken]].
 *
 * @see UserRefreshToken
 */
class UserRefreshTokenQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return UserRefreshToken[]|array
     */
    public function all($db = null): array
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return UserRefreshToken|array|null
     */
    public function one($db = null): UserRefreshToken|array|null
    {
        return parent::one($db);
    }
}
