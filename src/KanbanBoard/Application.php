<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;
use Michelf\Markdown;

class Application
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

    public function board(): array
    {
        $ms = [];
        $milestones = [];

        foreach ($this->repositories as $repository) {
            foreach ($this->github_client->milestones($repository) as $data) {


                $ms[$data['title']] = $data;
                $ms[$data['title']]['repository'] = $repository;
            }
        }

        ksort($ms);
        foreach ($ms as $name => $data) {
            $issues = $this->issues($data['repository'], $data['number']);
            $percent = self::percent($data['closed_issues'], $data['open_issues']);
            if ($percent) {
                $milestones[] = [
                    'milestone' => $name,
                    'url' => $data['html_url'],
                    'progress' => $percent,
                    'queued' => $issues['queued'],
                    'active' => $issues['active'],
                    'completed' => $issues['completed']
                ];
            }
        }
        return $milestones;
    }

    private function issues($repository, $milestone_id): array
    {
        $i = $this->github_client->issues($repository, $milestone_id);

        $issues = [];
        foreach ($i as $ii) {
            if (isset($ii['pull_request'])) {
                continue;
            }
            $issues[$ii['state'] === 'closed' ? 'completed' : (($ii['assignee']) ? 'active' : 'queued')][] = [
                'id' => $ii['id'],
                'number' => $ii['number'],
                'title' => $ii['title'],
                'body' => Markdown::defaultTransform($ii['body']),
                'url' => $ii['html_url'],
                'assignee' => (is_array($ii) && array_key_exists(
                        'assignee',
                        $ii
                    ) && !empty($ii['assignee'])) ? $ii['assignee']['avatar_url'] . '?s=16' : null,
                'paused' => $this->labelsMatch($ii, $this->paused_labels),
                'progress' => $this->percent(
                    substr_count(strtolower($ii['body']), '[x]'),
                    substr_count(strtolower($ii['body']), '[ ]')
                ),
                'closed' => $ii['closed_at']
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

    private function percent($complete, $remaining)
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
