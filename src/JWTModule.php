<?php

namespace sakhnovkrg\yii2\jwt;

use Yii;
use sakhnovkrg\yii2\jwt\models\BasicApplicationLoginForm;
use sakhnovkrg\yii2\jwt\models\AbstractLoginForm;
use sakhnovkrg\yii2\jwt\models\UserRefreshToken;
use sakhnovkrg\yii2\jwt\models\UserRefreshTokenQuery;
use sakhnovkrg\yii2\jwt\repositories\UserRefreshTokenRepository;
use sakhnovkrg\yii2\jwt\services\RefreshTokenService;
use sakhnovkrg\yii2\jwt\services\JWTService;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\Application as WebApplication;
use yii\console\Application as ConsoleApplication;

class JWTModule extends Module implements BootstrapInterface
{
    public $controllerNamespace = 'sakhnovkrg\yii2\jwt\controllers';
    public int $accessTokenExpirationMinutes = 5;
    public int $refreshTokenExpirationMinutes = 24*60;
    public string $jwtSecretKeyEnvVariable = 'JWT_SECRET';
    public string $jwtSecretKeyFilePathIfNoEnv = '@runtime/jwt.secret';
    public int $maxRefreshTokensForUser = 10;

    protected function registerDependencies(): void
    {
        $di = Yii::$container;

        $di->set(UserRefreshTokenRepository::class);

        $di->setSingleton(RefreshTokenService::class, function () use ($di) {
            $refreshTokenRepository = $di->get(UserRefreshTokenRepository::class);
            return new RefreshTokenService($refreshTokenRepository);
        });

        $di->setSingleton(JWTService::class, function () use ($di) {
            $refreshTokenService = $di->get(RefreshTokenService::class);
            return new JWTService($refreshTokenService);
        });

        $di->set(AbstractLoginForm::class, BasicApplicationLoginForm::class);
        $di->set(UserRefreshToken::class);
        $di->set(UserRefreshTokenQuery::class);
    }

    public function bootstrap($app): void
    {
        \Yii::setAlias('@yii2jwt', __DIR__);

        if(!Yii::$app->getModule('jwt-auth')) {
            $app->setModule('jwt-auth', ['class' => static::class]);
        }

        if ($app instanceof WebApplication) {
            $app->getUrlManager()->addRules([
                'POST auth/login' => 'jwt-auth/auth/login',
                'POST auth/logout' => 'jwt-auth/auth/logout',
                'POST auth/refresh' => 'jwt-auth/auth/refresh',
                'GET @me' => 'jwt-auth/auth/me',
            ], false);
        } elseif ($app instanceof ConsoleApplication) {
            $migrationPath = '@yii2jwt/migrations';
            $app->controllerMap['migrate']['migrationPath'][] = $migrationPath;
            if(!isset($app->controllerMap['migrate']['class'])) {
                $app->controllerMap['migrate']['class'] = \yii\console\controllers\MigrateController::class;
            }
        }

        $this->registerDependencies();
    }
}
