<?php

namespace Nghh\Lib\Wordpress\Utils\Admin\Github\Api;

class Issues extends AbstractAPI
{
    private $queryString;

    public function __construct($client)
    {
        $queryString = <<<'GRAPHQL'
            query GetRepositoryIssues($owner:String!, $repo: String!, $state: [IssueState!]) {
                repository(name: $repo, owner: $owner) {
                issues(states: $state, first: 20) {
                    nodes {
                    bodyHTML
                    createdAt
                    id
                    number
                    title
                    url
                    }
                }
                }
            }
            GRAPHQL;

        $this->client = $client;
    }

    public function is(string $state)
    {
    }
}
