<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Github\Api;

class GraphQL extends AbstractApi
{
    public function __construct($client)
    {
        $this->client = $client;
    }
}
