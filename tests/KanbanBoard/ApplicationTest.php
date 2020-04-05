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

    public function testBoardRepositoryWithMilestonesWithoutIssues()
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
}
