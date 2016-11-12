<?php
namespace SlimDash\Core;

use Firebase\JWT\JWT;
use Psr\Log\LogLevel;

class FirebaseAuth extends \Slim\Middleware\JwtAuthentication
{
    /**
     * Decode the token
     *
     * @param  string         $$token
     * @return object|boolean The JWT's payload as a PHP object or false in case of error
     */
    public function decodeToken($token)
    {
        try {
            JWT::$leeway = 8;
            $content     = file_get_contents("https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com");
            $kids        = json_decode($content, true);
            $jwt         = JWT::decode($token, $kids, array('RS256'));
            $fbpid       = getenv('FIREBASE_PROJECTID');
            $issuer      = 'https://securetoken.google.com/' . $fbpid;
            if ($jwt->aud != $fbpid) {
                $this->message = 'Invalid audience';
                $this->log(LogLevel::WARNING, $this->message, [$token]);
                return false;
            } elseif ($jwt->iss != $issuer) {
                $this->message = 'Invalid issuer';
                $this->log(LogLevel::WARNING, $this->message, [$token]);
                return false;
            } elseif (empty($jwt->sub)) {
                $this->message = 'Invalid user';
                $this->log(LogLevel::WARNING, $this->message, [$token]);
                return false;
            };
            return $token;
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            $this->log(LogLevel::WARNING, $exception->getMessage(), [$token]);
            return false;
        }
    }
}
