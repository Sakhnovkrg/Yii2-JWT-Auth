<?php

namespace sakhnovkrg\yii2\jwt\models;

use yii\web\BadRequestHttpException;
use yii\web\IdentityInterface;

class BasicApplicationLoginForm extends AbstractLoginForm
{
    private ?IdentityInterface $_identity = null;

    public function rules(): array
    {
        return [
            [['login', 'password'], 'required'],
            ['password', 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params): void
    {
        if (!$this->hasErrors()) {
            $identity = $this->getIdentity();

            if (!$identity || !$identity->validatePassword($this->password)) {
                throw new BadRequestHttpException('Incorrect login or password.');
            }
        }
    }

    public function getIdentity(): ?IdentityInterface
    {
        if (!$this->_identity) {
            $this->_identity = call_user_func([\Yii::$app->user->identityClass, 'findByUsername'], $this->login);
        }

        return $this->_identity;
    }
}

