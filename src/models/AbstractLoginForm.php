<?php

namespace sakhnovkrg\yii2\jwt\models;

use yii\base\Model;
use yii\web\IdentityInterface;

abstract class AbstractLoginForm extends Model
{
    public ?string $login = null;
    public ?string $password = null;

    abstract public function getIdentity(): ?IdentityInterface;
}
