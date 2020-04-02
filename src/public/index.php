<?php declare(strict_types=1);

use App\KanbanBoard\Authentication;
use App\KanbanBoard\GithubActual;
use App\KanbanBoard\Application;
use App\KanbanBoard\GithubClient;
use App\Utils;
use Dotenv\Dotenv;
use KanbanBoard\Login;

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$repositories = explode('|', Utils::env('GH_REPOSITORIES'));

$authentication = new Authentication();
$token = $authentication->login();
$github = new GithubClient($token, Utils::env('GH_ACCOUNT'));
$board = new Application($github, $repositories, ['waiting-for-feedback']);
$data = $board->board();

Utils::dump($data);exit();
$m = new Mustache_Engine([
	'loader' => new Mustache_Loader_FilesystemLoader('../views'),
]);
echo $m->render('index', ['milestones' => $data]);
