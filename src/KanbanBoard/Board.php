<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;
use Michelf\Markdown;

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

    public function data(): array
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
            $issues[$issue['state'] === 'closed' ? 'completed' : (($issue['assignee']) ? 'active' : 'queued')][] = [
                'id' => $issue['id'],
                'number' => $issue['number'],
                'title' => $issue['title'],
                'body' => Markdown::defaultTransform($issue['body']),
                'url' => $issue['html_url'],
                'assignee' => (is_array($issue) && array_key_exists(
                        'assignee',
                        $issue
                    ) && !empty($issue['assignee'])) ? $issue['assignee']['avatar_url'] . '?s=16' : null,
                'paused' => $this->labelsMatch($issue, $this->paused_labels),
                'progress' => $this->percent(
                    substr_count(strtolower($issue['body']), '[x]'),
                    substr_count(strtolower($issue['body']), '[ ]')
                ),
                'closed' => $issue['closed_at']
            ];
        }

        if (isset($issues['active'])) {
            usort(
                $issues['active'],
                function ($a, $b) {
                    return count($a['paused']) - count($b['paused']) === 0 ? strcmp($a['title'], $b['title']) : count(
                            $a['paused']
                        ) - count($b['paused']);
                }
            );
        }

        return $issues;
    }

    private function labelsMatch($issue, $needles)
    {
        if (Utils::hasValue($issue, 'labels')) {
            foreach ($issue['labels'] as $label) {
                if (in_array($label['name'], $needles)) {
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
            $percent = ($complete OR $remaining) ? round($complete / $total * 100) : 0;
            return [
                'total' => $total,
                'complete' => $complete,
                'remaining' => $remaining,
                'percent' => $percent
            ];
        }
        return [];
    }
}
