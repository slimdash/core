<?php
namespace SlimDash\Core;

use Firebase\JWT\JWT;
use Psr\Log\LogLevel;

class FirebaseAuth extends \Slim\Middleware\JwtAuthentication
{
    /**
     * Decode the token
     *
     * @param string $$token
     * @return object|boolean The JWT's payload as a PHP object or false in case of error
     */
    public function decodeToken($token) {
        $rst = $this->decodeToken2($token);

        if (is_null($rst["message"])) {
            return $rst["decoded"];
        }

        return false;
    }

    /**
     * Decode the token
     *
     * @param  string         $token
     * @return object          
     */
    public function decodeToken2($token)
    {
        $rst = [
            "token" => false,
            "expired" => false,
            "message" => null,
            "decoded" => null
        ];

        try {
            \Firebase\JWT\JWT::$leeway = 8;
            $content     = file_get_contents("https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com");
            $kids        = json_decode($content, true);
            $jwt         = \Firebase\JWT\JWT::decode($token, $kids, array('RS256'));
            $fbpid       = env('FIREBASE_PROJECTID');
            $issuer      = 'https://securetoken.google.com/' . $fbpid;
            $rst["token"] = $token;
            $rst["decoded"] = $jwt;

            if ($jwt->aud != $fbpid) {
                $rst["message"] = 'invalid audience ' . $jwt->aud;
            } elseif ($jwt->iss != $issuer) {
                $rst["message"] = 'invalid issuer ' . $jwt->iss;
            } elseif (empty($jwt->sub)) {
                $rst["message"] = 'invalid sub ' . $jwt->sub;
            };
        } catch (\Firebase\JWT\ExpiredException $ee) {
            $rst["expired"] = true;
            $rst["message"] = 'token has expired';
            // we want to keep the token for use later
        } catch (\Exception $e) {
            $rst["message"] = $e->getMessage();
        }

        return $rst;
    }
}
