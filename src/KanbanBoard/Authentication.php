<?php declare(strict_types=1);

namespace App\KanbanBoard;

use App\Utils;

class Authentication
{
    private string $client_id;
    private string $client_secret;

    public function __construct(string $clientId, string $clientSecret)
    {
        $this->client_id = $clientId;
        $this->client_secret = $clientSecret;
    }

    // TODO possibility to logaut in front
    public function logout()
    {
        unset($_SESSION['gh-token']);
    }

    public function login()
    {
        session_start();
        $token = null;
        if (array_key_exists('gh-token', $_SESSION)) {
            $token = $_SESSION['gh-token'];
        } else {
            if (Utils::hasValue($_GET, 'code')
                && Utils::hasValue($_GET, 'state')
                && $_SESSION['redirected']) {
                $_SESSION['redirected'] = false;
                $token = $this->returnsFromGithub($_GET['code']);
            } else {
                $_SESSION['redirected'] = true;
                $this->redirectToGithub();
            }
        }
//        $this->logout();
        $_SESSION['gh-token'] = $token;

        return $token;
    }

    private function returnsFromGithub($code)
    {
        $url = 'https://github.com/login/oauth/access_token';
        $data = [
            'code' => $code,
            'state' => 'LKHYgbn776tgubkjhk', // @TODO-1 random
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
            die('Error');
        }
        $result = explode('=', explode('&', $result)[0]);
        array_shift($result);
        return array_shift($result);
    }

    private function redirectToGithub()
    {
        $url = 'Location: https://github.com/login/oauth/authorize';
        $url .= '?client_id=' . $this->client_id;
        $url .= '&scope=repo';
        $url .= '&state=LKHYgbn776tgubkjhk'; // @TODO-1 random
        header($url);
        exit();
    }
}
