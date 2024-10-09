<?php

namespace sakhnovkrg\yii2\jwt\models;

use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "user_refresh_token".
 *
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property string $ip
 * @property string $user_agent
 * @property int $created_at
 */
class UserRefreshToken extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_refresh_token}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'token', 'ip', 'user_agent', 'created_at'], 'required'],
            [['user_id'], 'integer'],
            [['token'], 'string', 'max' => 32],
            [['ip'], 'string', 'max' => 50],
            [['user_agent'], 'string', 'max' => 1000],
            [['created_at'], 'safe'],
            [['token'], 'unique'],
        ];
    }

    /**
     * @inheritDoc
     * @return object|UserRefreshTokenQuery|\yii\db\ActiveQuery
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public static function find()
    {
        return \Yii::$container->get(UserRefreshTokenQuery::class, [get_called_class()]);
    }
}
