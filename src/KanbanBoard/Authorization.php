<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;
use RuntimeException;

class Authorization
{
    private string $client_id;
    private string $client_secret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->client_id = $clientId;
        $this->client_secret = $clientSecret;
    }

    public function accessToken()
    {
        session_start();
        $token = null;
        if (Utils::hasValue($_SESSION, 'gh-token')) {
            $token = $_SESSION['gh-token'];
        } else {
            if ($this->isAuthorized()) {
                $_SESSION['redirected'] = false;
                $token = $this->accessTokenFromGithub();
            } else {
                $_SESSION['redirected'] = true;
                $this->authorizeWithGithub();
            }
        }

        $_SESSION['gh-token'] = $token;

        return $token;
    }

    public function isAuthorized(): bool
    {
        return Utils::hasValue($_GET, 'code')
            && Utils::hasValue($_GET, 'state')
            && $_SESSION['redirected'];
    }

    private function authorizeWithGithub()
    {
        $_SESSION['gh-state'] = (string)random_int(PHP_INT_MIN, PHP_INT_MAX);

        $url = 'Location: https://github.com/login/oauth/authorize';
        $url .= '?client_id=' . $this->client_id;
        $url .= '&scope=repo';
        $url .= '&state=' . $_SESSION['gh-state'];
        header($url);
        exit();
    }

    private function accessTokenFromGithub()
    {
        if ($_GET['state'] !== $_SESSION['gh-state']) {
            throw new RuntimeException(
                'Security issue. State param used under authorization does not match.'
            );
        }

        $url = 'https://github.com/login/oauth/access_token';
        $data = [
            'code' => $_GET['code'],
            'state' => $_GET['state'],
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            throw new RuntimeException('Empty result when request for access_token');
        }

        return $this->extractAccessKey($result);
    }

    private function extractAccessKey(string $result): string
    {
        parse_str($result, $parsed_string);

        if (isset($parsed_string['access_token'])) {
            return $parsed_string['access_token'];
        }

        throw new RuntimeException('access_token can not be empty');
    }
}
