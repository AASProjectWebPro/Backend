<?php
defined('BASEPATH') or exit('No direct script access allowed');


use Firebase\JWT\JWT as JWTLib;
use Firebase\JWT\Key;

class Jwt
{
    private $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->config("jwt");
    }

    public function encode($data)
    {
        $key = $this->CI->config->item('jwt_key');
        $algortima = $this->CI->config->item('jwt_algorithm');
        $issuer = $this->CI->config->item('jwt_issuer');
        $audience = $this->CI->config->item('jwt_audience');
        $expire = $this->CI->config->item('jwt_expire');
        $token = [
            'iss' => $issuer,
            'aud' => $audience,
            'iat' => time(),
            'exp' => time() + $expire,
            'data' => $data,
        ];
        return JWTLib::encode($token, $key, $algortima);
    }
}
