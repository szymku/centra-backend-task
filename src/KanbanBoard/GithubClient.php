<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;

class GithubClient
{
    private $client;
    private $milestone_api;
    private $account;

    public function __construct($token, $account)
    {
        $this->account = $account;
        $this->client= new \Github\Client(new \Github\HttpClient\CachedHttpClient(['cache_dir' => '/tmp/github-api-cache']));
        $this->milestone_api = $this->client->api('issues')->milestones();
    }

    public function milestones($repository)
    {
        return $this->milestone_api->all($this->account, $repository);
    }

    public function issues($repository, $milestone_id)
    {
        $issue_parameters = ['milestone' => $milestone_id, 'state' => 'all'];
        return $this->client->api('issue')->all($this->account, $repository, $issue_parameters);
    }
}
