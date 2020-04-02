<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;
use Github\Client;
use vierbergenlars\SemVer\version;

use vierbergenlars\SemVer\expression;
use vierbergenlars\SemVer\SemVerException;
use \Michelf\Markdown;

class Application
{
    private GithubClient $github;
    private array $repositories;
    private array $paused_labels;

    public function __construct(GithubClient $github, array $repositories, array $paused_labels = [])
    {
        $this->github = $github;
        $this->repositories = $repositories;
        $this->paused_labels = $paused_labels;
    }

    public function board()
    {
        $ms = [];
        foreach ($this->repositories as $repository) {
            foreach ($this->github->milestones($repository) as $data) {
                $ms[$data['title']] = $data;
                $ms[$data['title']]['repository'] = $repository;
            }
        }

        ksort($ms);
        foreach ($ms as $name => $data) {
            $issues = $this->issues($data['repository'], $data['number']);
            $percent = self::_percent($data['closed_issues'], $data['open_issues']);
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

    private function issues($repository, $milestone_id)
    {
        $i = $this->github->issues($repository, $milestone_id);

        $issues = [];
        foreach ($i as $ii) {
            if (isset($ii['pull_request'])) {
                continue;
            }
            $issues[$ii['state'] === 'closed' ? 'completed' : (($ii['assignee']) ? 'active' : 'queued')][] = [
                'id' => $ii['id'], 'number' => $ii['number'],
                'title' => $ii['title'],
                'body' => Markdown::defaultTransform($ii['body']),
                'url' => $ii['html_url'],
                'assignee' => (is_array($ii) && array_key_exists('assignee', $ii) && !empty($ii['assignee'])) ? $ii['assignee']['avatar_url'] . '?s=16' : NULL,
                'paused' => self::labels_match($ii, $this->paused_labels),
                'progress' => self::_percent(
                    substr_count(strtolower($ii['body']), '[x]'),
                    substr_count(strtolower($ii['body']), '[ ]')),
                'closed' => $ii['closed_at']
            ];
        }


        if (isset($issues['active'])) {
            usort($issues['active'], function ($a, $b) {
                return count($a['paused']) - count($b['paused']) === 0 ? strcmp($a['title'], $b['title']) : count($a['paused']) - count($b['paused']);
            });
        }

        return $issues;
    }

    private static function _state($issue)
    {
        if ($issue['state'] === 'closed')
            return 'completed';
        else if (Utils::hasValue($issue, 'assignee') && count($issue['assignee']) > 0)
            return 'active';
        else
            return 'queued';
    }

    private static function labels_match($issue, $needles)
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

    private static function _percent($complete, $remaining)
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
