<?php declare(strict_types=1);

namespace tests\KanbanBoard;

use App\KanbanBoard\Application;
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
        $application = new Application($gitlabClientMock, [], []);

        $this->assertEquals([], $application->board());
    }
}
