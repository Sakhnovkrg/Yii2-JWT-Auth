<?php

namespace sakhnovkrg\yii2\jwt\filters;

use Yii;
use yii\filters\auth\HttpBearerAuth;
class JWTAuthenticator extends HttpBearerAuth
{
    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);

        if ($authHeader !== null) {
            if ($this->pattern !== null) {
                if (preg_match($this->pattern, $authHeader, $matches)) {
                    $authHeader = $matches[1];
                } else {
                    return null;
                }
            }

            $class = Yii::$app->user->identityClass;

            try {
                $method = 'findIdentityByJWTToken';
                if (method_exists($class, $method)) {
                    $identity = call_user_func([$class, $method], $authHeader);
                } else {
                    throw new \RuntimeException("Method {$method} does not exist in class {$class}.");
                }
            } catch (\Error $e) {
                throw new \RuntimeException(sprintf(
                    'Error in class %s: Ensure the UseJWT trait is used, and that the findIdentityByJWTToken method is properly implemented. Original error: %s',
                    $class,
                    $e->getMessage()
                ));
            }

            if ($identity && Yii::$app->user->login($identity)) {
                return $identity;
            }

            if ($identity === null) {
                $this->challenge($response);
                $this->handleFailure($response);
            }

            return $identity;
        }

        return null;
    }
}
