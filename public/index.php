<?php declare(strict_types=1);

use App\KanbanBoard\Authorization;
use App\KanbanBoard\Board;
use App\KanbanBoard\GithubClient;
use App\Utils;
use Dotenv\Dotenv;
use Github\Client;
use Github\HttpClient\CachedHttpClient;

require __DIR__ . '/../vendor/autoload.php';

ini_set('session.cookie_httponly', "1");
//ini_set('session.cookie_secure', "1");

try {
    Dotenv::createImmutable(__DIR__ . '/../')->load();

    $github_account = Utils::env('GH_ACCOUNT');
    $client_id = Utils::env('GH_CLIENT_ID');
    $client_secret = Utils::env('GH_CLIENT_SECRET');
    $repositories = explode('|', Utils::env('GH_REPOSITORIES'));
    $paused_labels = explode('|', Utils::env('PAUSED_LABELS', ''));

    $token = (new Authorization($client_id, $client_secret))->accessToken();

    $github_api_client = new Client(new CachedHttpClient());
    $github_api_client->authenticate($token, null, Client::AUTH_HTTP_TOKEN);

    $github = new GithubClient($github_account, $github_api_client);
    $data = (new Board($github, $repositories, $paused_labels))->data();

    echo (new Mustache_Engine(
        [
            'loader' => new Mustache_Loader_FilesystemLoader('views'),
        ]
    ))->render('index', ['milestones' => $data]);
} catch (\Throwable $e) {
    Utils::logError(__DIR__ . '/../log/log.log', $e);
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Something went wrong. Please come back in a moment.';
}