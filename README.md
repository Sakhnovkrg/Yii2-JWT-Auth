# Yii2-JWT-Auth
An easy to use and fully customizable JWT authentication module for your Yii2 application.

## Usage
Minimal example with [Yii2 Basic Application](https://github.com/yiisoft/yii2-app-basic)

1. Install extension
```bash
composer require --prefer-dist sakhnovkrg/yii2-jwt-auth "@dev"
```
2. Run migrations
```bash
php yii migrate
```
3. Add trait to your user model
```php
<?php

namespace app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    use \sakhnovkrg\yii2\jwt\traits\JWTAuthTrait;
    // ...
}
```
4. Enable pretty urls
```php
'components' => [
  'urlManager' => [
      'enablePrettyUrl' => true,
      'showScriptName' => false,
      'rules' => [
      ],
  ],
  // ...
]
```
Done  ¯\\_(ツ)_/¯

To protect your controllers you can use behaviour
```php
public function behaviors()
{
    return [
        [
            'class' => \sakhnovkrg\yii2\jwt\filters\JWTAuthenticator::class,
            'except' => ['safeAction']
        ]
    ];
}
```
## Endpoints
```
Method: POST
URL: /auth/login
Body: {
    "login": "demo",
    "password": "demo"
}
Result: Access token and refresh token in httponly cookie

Method: GET
URL: /@me
Header: Authorization: Bearer %Access token%
Result: Authentificated user info

Method: POST
URL: /auth/refresh
Cookie: Refresh token
Result: New access and refresh tokens

Method: POST
URL: /auth/logout
Header: Authorization: Bearer %Access token%
Result: Remove refresh token cookie
```
The Postman collection is located in the root of the repository.
## Customize
Module settings
```php
'modules' => [
    'jwt-auth' => [
        'class' => \sakhnovkrg\yii2\jwt\JWTModule::class,
        'controllerNamespace' => 'sakhnovkrg\yii2\jwt\controllers',
        'accessTokenExpirationMinutes' => 5,
        'refreshTokenExpirationMinutes' => 24*60,
        'jwtSecretKeyEnvVariable' => 'JWT_SECRET',
        // If the environment variable is not set, the JWT secret key will be automatically generated at the specified path
        'jwtSecretKeyFilePathIfNoEnv' => '@runtime/jwt.secret',
        // Refresh tokens abuse protection
        'maxRefreshTokensForUser' => 10 
    ]
],
```
You can also override any model, service, or repository using dependency injection.
```php
'bootstrap' => ['log', \app\components\Bootstrap::class],
```
```php
<?php

namespace app\components;

use app\models\MyLoginForm;
use app\services\MyRefreshTokenService;
use sakhnovkrg\yii2\jwt\models\AbstractLoginForm;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    public function bootstrap($app)
    {
        $di = Yii::$container;
        // By default, the module is configured to work with the user model from the Yii2 Basic Application, so in a real application, you will need to customize the form for your own user model.
        $di->set(AbstractLoginForm::class, MyLoginForm::class);
        $di->setSingleton(RefreshTokenService::class, function () use ($di) {
            $refreshTokenRepository = $di->get(UserRefreshTokenRepository::class);
            return new MyRefreshTokenService($this, $refreshTokenRepository);
        });
        // etc.
    }
}
```
