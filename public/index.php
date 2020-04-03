<?php declare(strict_types=1);

use App\KanbanBoard\Application;
use App\KanbanBoard\Authentication;
use App\KanbanBoard\GithubClient;
use App\Utils;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

Dotenv::createImmutable(__DIR__ . '/../')->load();

$repositories = explode('|', Utils::env('GH_REPOSITORIES'));
$github_account = Utils::env('GH_ACCOUNT');
$paused_labels = explode('|', Utils::env("PAUSED_LABELS"));
$client_id = Utils::env('GH_CLIENT_ID');
$client_secret = Utils::env('GH_CLIENT_SECRET');

(new Authentication($client_id, $client_secret))->login();
$github = new GithubClient($github_account);
$app = new Application($github, $repositories, $paused_labels);
$data = $app->board();

$m = new Mustache_Engine(
    [
        'loader' => new Mustache_Loader_FilesystemLoader('views'),
    ]
);
echo $m->render('index', ['milestones' => $data]);
