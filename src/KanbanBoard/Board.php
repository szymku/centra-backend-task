<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;

class Board
{
    private GithubClient $github_client;
    private array $repositories;
    private array $paused_labels;

    public function __construct(GithubClient $github_client, array $repositories, array $paused_labels = [])
    {
        $this->github_client = $github_client;
        $this->repositories = $repositories;
        $this->paused_labels = $paused_labels;
    }

    public function milestones(): array
    {
        $milestones = [];

        foreach ($this->repositories as $repository) {
            foreach ($this->github_client->milestones($repository) as $data) {
                $issues = $this->issues($repository, $data['number']);
                $percent = $this->percent($data['closed_issues'], $data['open_issues']);
                if ($percent) {
                    $milestones[$data['title']] = [
                        'milestone' => $data['title'],
                        'url' => $data['html_url'],
                        'progress' => $percent,
                        'queued' => $issues['queued'],
                        'active' => $issues['active'],
                        'completed' => $issues['completed']
                    ];
                }
            }
        }
        ksort($milestones);

        return array_values($milestones);
    }

    private function issues(string $repository, int $milestone_id): array
    {
        $issues = [];
        foreach ($this->github_client->issues($repository, $milestone_id) as $issue) {
            if (isset($issue['pull_request'])) {
                continue;
            }
            $issues[$this->adjustIssueState($issue)][] = [
                'title' => $issue['title'],
                'url' => $issue['html_url'],
                'assignee' => $this->adjustAvatar($issue),
                'paused' => $this->labelsMatch($issue),
            ];
        }

        if (isset($issues['active'])) {
            $issues['active'] = $this->movePausedIssuesDownAndSortAlphabetically($issues['active']);
        }

        return $issues;
    }

    private function adjustIssueState(array $issue): string
    {
        return $issue['state'] === 'closed' ? 'completed' : (($issue['assignee']) ? 'active' : 'queued');
    }

    private function adjustAvatar(array $issue): ?string
    {
        return Utils::hasValue($issue, 'assignee') ? $issue['assignee']['avatar_url'] . '?s=16' : null;
    }

    private function labelsMatch(array $issue): array
    {
        if (Utils::hasValue($issue, 'labels')) {
            foreach ($issue['labels'] as $label) {
                if (in_array($label['name'], $this->paused_labels)) {
                    return [$label['name']];
                }
            }
        }

        return [];
    }

    private function percent(int $complete, int $remaining): array
    {
        $total = $complete + $remaining;
        if ($total > 0) {
            return ['percent' => round($complete / $total * 100)];
        }

        return [];
    }

    private function movePausedIssuesDownAndSortAlphabetically(array $activeIssues): array
    {
        usort(
            $activeIssues,
            function ($a, $b) {
                return count($a['paused']) - count($b['paused']) === 0 ?
                    strcmp($a['title'], $b['title']) :
                    count($a['paused']) - count($b['paused']);
            }
        );

        return $activeIssues;
    }
}
