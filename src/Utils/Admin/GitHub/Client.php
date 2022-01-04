<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Github;

class Client
{
    public $client;

    private $user;
    private $pass;
    public $token;
    private $owner;
    private $repository;

    private static $instance;

    public function withAuth(array $auth)
    {
        $this->user =   (isset($auth['user'])) ? $auth['user'] : null;
        $this->pass =   (isset($auth['pass'])) ? $auth['pass'] : null;
        $this->token =  (isset($auth['token'])) ? $auth['token'] : null;

        return $this;
    }

    public function owner(string $owner)
    {
        $this->owner = $owner;
    }

    public function repository(string $repository)
    {
        $this->repository = $repository;
    }

    public function api(string $name)
    {
        switch ($name) {
            case 'repos':
                $api = new Api\Repos($this);
                break;
            case 'issues':
                $api = new Api\Issues($this);
                break;
            case 'graphql':
                $api = new Api\GraphQL($this);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Undefined api instance called: "%s"', $name));
                break;
        }

        return $api;
    }

    public static function create()
    {
        if (is_null(static::$instance)) {
            return static::$instance = new static();
        }
        return static::$instance;
    }
}
