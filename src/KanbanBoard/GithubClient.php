<?php declare(strict_types=1);

namespace App\KanbanBoard;

use Github\Api\Issue;
use Github\Client;
use Github\HttpClient\CachedHttpClient;

class GithubClient
{
    private Client $client;
    private string $account;

    public function __construct(string $account)
    {
        $this->account = $account;
        $this->client = new Client(new CachedHttpClient(['cache_dir' => '/tmp/github-api-cache']));
    }

    public function milestones(string $repository): array
    {
        return $this->issueApi()->milestones()->all($this->account, $repository);
    }

    public function issues(string $repository, int $milestone_id): array
    {
        return $this->issueApi()->all($this->account, $repository, ['milestone' => $milestone_id, 'state' => 'all']);
    }

    private function issueApi(): Issue
    {
        return $this->client->api('issue');
    }
}
