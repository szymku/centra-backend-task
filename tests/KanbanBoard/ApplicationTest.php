<?php declare(strict_types=1);

namespace tests\KanbanBoard;

use App\KanbanBoard\Board;
use App\KanbanBoard\GithubClient;
use Mockery;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
    }

    public function testBoardNoRepositoryGiven()
    {
        $gitlabClientMock = Mockery::mock(GithubClient::class);
        $application = new Board($gitlabClientMock, [], []);

        $this->assertEquals([], $application->milestones());
    }

    public function testBoardWhenRepositoryHasNoMilestones()
    {
        $gitlabClientMock = Mockery::mock(GithubClient::class);

        $gitlabClientMock->shouldReceive('milestones')
            ->once()
            ->with('my_repository')
            ->andReturn([]);

        $application = new Board($gitlabClientMock, ['my_repository'], []);

        $this->assertEquals([], $application->milestones());
    }

    public function testBoardRepositoryWithMilestoneWithoutIssues()
    {
        $gitlabClientMock = Mockery::mock(GithubClient::class);

        $gitlabClientMock->shouldReceive('milestones')
            ->once()
            ->with('my_repository')
            ->andReturn(
                [
                    [

                        'title' => 'Title 1',
                        'number' => 1,
                        'closed_issues' => 0,
                        'open_issues' => 0,
                    ]
                ]
            );

        $gitlabClientMock->shouldReceive('issues')
            ->once()
            ->with('my_repository', 1)
            ->andReturn([]);

        $application = new Board($gitlabClientMock, ['my_repository'], []);

        $this->assertEquals([], $application->milestones());
    }

    public function testBoardRepositoryWithMilestoneWithOneClosedIssue()
    {
        $gitlabClientMock = Mockery::mock(GithubClient::class);

        $gitlabClientMock->shouldReceive('milestones')
            ->once()
            ->with('my_repository')
            ->andReturn($this->milestoneWithClosedIssue());

        $gitlabClientMock->shouldReceive('issues')
            ->once()
            ->with('my_repository', 1)
            ->andReturn($this->closedIssue());

        $application = new Board($gitlabClientMock, ['my_repository'], []);

        $this->assertEquals($this->milestonesResultForClosedIssue(), $application->milestones());
    }

    private function milestoneWithClosedIssue(): array
    {
        return [
            [
                'title' => 'Code should work',
                "html_url" => 'https://github.com/szymku/centra-backend-task/milestone/1',
                'number' => 1,
                'closed_issues' => 1,
                'open_issues' => 0,
            ]
        ];
    }

    private function closedIssue(): array
    {
        return [
            [
                'title' => 'Run existing code with minimum changes',
                'html_url' => 'https://api.github.com/repos/szymku/centra-backend-task/issues/5',
                'assignee' => ['avatar_url' => 'https://avatars2.githubusercontent.com/u/5146603?v=4'],
                'labels' => [],
                'state' => 'closed'
            ]
        ];
    }

    private function milestonesResultForClosedIssue()
    {
        return [
            0 => [
                'milestone' => 'Code should work',
                'url' => 'https://github.com/szymku/centra-backend-task/milestone/1',
                'progress' => ['percent' => 100.0],
                'queued' => null,
                'active' => null,
                'completed' => [
                    0 =>
                        [
                            'title' => 'Run existing code with minimum changes',
                            'url' => 'https://api.github.com/repos/szymku/centra-backend-task/issues/5',
                            'assignee' => 'https://avatars2.githubusercontent.com/u/5146603?v=4?s=16',
                            'paused' => [],
                        ],
                ],
            ],
        ];
    }
}