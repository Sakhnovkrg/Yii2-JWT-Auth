<?php

namespace sakhnovkrg\yii2\jwt\services;

use Yii;
use Firebase\JWT\JWT;
use sakhnovkrg\yii2\jwt\JWTModule;
use yii\web\IdentityInterface;
use \yii\web\Cookie;

class JWTService
{
    private JWTModule $module;
    private RefreshTokenService $refreshTokenService;
    private ?string $_secret = null;

    public function generateTokensResponse(IdentityInterface $model): array
    {
        $this->refreshTokenService->limitTokens($model);
        $refreshToken = $this->refreshTokenService->generateRefreshToken($model)->token;

        Yii::$app->response->cookies->add(new Cookie([
            'name' => 'refresh_token',
            'value' => $refreshToken,
            'httpOnly' => true,
            'secure' => YII_ENV_PROD || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
            'sameSite' => 'Lax',
            'expire' => time() + $this->module->refreshTokenExpirationMinutes * 60
        ]));

        return [
            'accessToken' => $this->generateJWTForIdentity($model),
            'expires' => (new \DateTime())
                ->modify('+' . $this->module->accessTokenExpirationMinutes . ' minutes')
                ->format('Y-m-d\TH:i:sO')
        ];
    }

    public function generateJWTForIdentity(IdentityInterface $user): string
    {
        $accessTokenPayload = [
            'iat' => time(),
            'exp' => time() + $this->module->accessTokenExpirationMinutes * 60,
            'id' => $user->getId()
        ];

        return JWT::encode($accessTokenPayload, $this->getJWTSecret(), 'HS256');
    }

    public function getJWTSecret(): string
    {
        if($this->_secret) return $this->_secret;

        if (isset($_ENV[$this->module->jwtSecretKeyEnvVariable])) {
            return $this->_secret = $_ENV[$this->module->jwtSecretKeyEnvVariable];
        }

        $jwtSecretPath = \Yii::getAlias($this->module->jwtSecretKeyFilePathIfNoEnv);

        if (file_exists($jwtSecretPath)) {
            $secret = file_get_contents($jwtSecretPath);
            if (empty($secret)) {
                throw new \RuntimeException('Failed to read the jwt.secret file.');
            }
            return $this->_secret = $secret;
        }

        $secret = \Yii::$app->security->generateRandomString();

        if (file_put_contents($jwtSecretPath, $secret) === false) {
            throw new \RuntimeException('Failed to write the jwt.secret file.');
        }

        return $this->_secret = $secret;
    }

    public function __construct(RefreshTokenService $refreshTokenService)
    {
        $this->module = Yii::$app->getModule('jwt-auth');
        $this->refreshTokenService = $refreshTokenService;
    }
}
