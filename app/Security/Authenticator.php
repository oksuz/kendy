<?php

namespace Application\Security;

use Application\Repository\UserRepository;

class Authenticator
{
    protected $userRepository;

    private $CONSUMER_KEYS = [
        "app" => "5579fe912e6fa05b64ebb8e9ffe490f7eedb295f"
    ];

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function authenticateToken($accessToken)
    {
        return null;
    }

    public function consumerKeyIsValid($key)
    {
        return !is_numeric($key) && in_array($key, $this->CONSUMER_KEYS, true);
    }
}