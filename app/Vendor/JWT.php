<?php

namespace App\Vendor;

/**
 * Implementación simple de JWT para desarrollo sin Composer
 * Basada en firebase/php-jwt
 */
class JWT
{
    /**
     * Codificar un JWT
     */
    public static function encode($payload, $key, $alg = 'HS256')
    {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        
        $segments = [];
        $segments[] = static::urlsafeB64Encode(json_encode($header));
        $segments[] = static::urlsafeB64Encode(json_encode($payload));
        
        $signing_input = implode('.', $segments);
        $signature = static::sign($signing_input, $key, $alg);
        $segments[] = static::urlsafeB64Encode($signature);
        
        return implode('.', $segments);
    }
    
    /**
     * Decodificar un JWT
     */
    public static function decode($jwt, $key, $alg = 'HS256')
    {
        $segments = explode('.', $jwt);
        
        if (count($segments) != 3) {
            throw new \Exception('Wrong number of segments');
        }
        
        list($headb64, $bodyb64, $cryptob64) = $segments;
        
        $header = json_decode(static::urlsafeB64Decode($headb64), true);
        $payload = json_decode(static::urlsafeB64Decode($bodyb64), true);
        $sig = static::urlsafeB64Decode($cryptob64);
        
        if ($header['alg'] !== $alg) {
            throw new \Exception('Algorithm not allowed');
        }
        
        // Verificar firma
        $signing_input = $headb64 . '.' . $bodyb64;
        if (!static::verify($signing_input, $sig, $key, $alg)) {
            throw new \Exception('Signature verification failed');
        }
        
        // Verificar expiración
        if (isset($payload['exp']) && time() >= $payload['exp']) {
            throw new \Exception('Expired token');
        }
        
        return $payload;
    }
    
    /**
     * Firmar datos
     */
    protected static function sign($msg, $key, $alg = 'HS256')
    {
        $methods = [
            'HS256' => 'sha256',
            'HS384' => 'sha384',
            'HS512' => 'sha512',
        ];
        
        if (!isset($methods[$alg])) {
            throw new \Exception('Algorithm not supported');
        }
        
        return hash_hmac($methods[$alg], $msg, $key, true);
    }
    
    /**
     * Verificar firma
     */
    protected static function verify($msg, $signature, $key, $alg = 'HS256')
    {
        $hash = static::sign($msg, $key, $alg);
        return hash_equals($signature, $hash);
    }
    
    /**
     * Codificar en base64 URL-safe
     */
    protected static function urlsafeB64Encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    
    /**
     * Decodificar desde base64 URL-safe
     */
    protected static function urlsafeB64Decode($input)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}
