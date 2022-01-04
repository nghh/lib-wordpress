<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Github\Api;

abstract class AbstractApi
{
    protected $client;
    protected $query;
    protected $endpoint = 'https://api.github.com/graphql';

    public function execute($query, array $variables = [])
    {

        $headers = $this->getHeaders();

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => $headers,
                'content' => json_encode(['query' => $query, 'variables' => $variables]),
            ]
        ]);

        $data = @file_get_contents($this->endpoint, false, $context);

        if (false === $data) {
            $error = error_get_last();
            throw new \ErrorException($error['message'], $error['type']);
        }

        // var_dump($data);
        // die();
        return json_decode($data, true);
    }

    private function getHeaders()
    {
        $headers = ['Content-Type: application/json', 'User-Agent: NGHH\'s minimal GraphQL client'];

        if ($this->client->token) {
            $headers[] = "Authorization: bearer " . $this->client->token;
        }

        return $headers;
    }
}
