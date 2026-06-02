<?php

require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';

class AccountController
{
    private $accountModel;
    private $db;
    private $githubConfig;
    private $googleConfig;


    public function __construct()
    {
        $this->db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($this->db);
        $this->githubConfig = require 'app/config/github.php';
        $this->googleConfig = require 'app/config/google.php';
    }

    public function google()
    {
        if (strpos($this->googleConfig['client_id'], 'DIEN_') === 0) {
            echo 'Chua cau hinh Google Client ID trong app/config/google.php.';
            return;
        }

        $params = [
            'client_id' => $this->googleConfig['client_id'],
            'redirect_uri' => $this->googleConfig['redirect_uri'],
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
        ];

        header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
        exit;
    }

    public function googleCallback()
    {
        $code = $_GET['code'] ?? '';

        if ($code === '') {
            echo 'Google khong tra ve code.';
            return;
        }

        $tokenResponse = $this->postForm('https://oauth2.googleapis.com/token', [
            'client_id' => $this->googleConfig['client_id'],
            'client_secret' => $this->googleConfig['client_secret'],
            'code' => $code,
            'redirect_uri' => $this->googleConfig['redirect_uri'],
            'grant_type' => 'authorization_code',
        ]);

        $accessToken = $tokenResponse['access_token'] ?? '';

        if ($accessToken === '') {
            echo 'Khong lay duoc access token tu Google.';
            return;
        }

        $googleUser = $this->getJson('https://www.googleapis.com/oauth2/v2/userinfo', $accessToken);

        $googleId = (string) ($googleUser['id'] ?? '');
        $email = $googleUser['email'] ?? null;
        $fullname = $googleUser['name'] ?? ($email ?? 'Google User');
        $avatar = $googleUser['picture'] ?? '';

        if ($googleId === '' || empty($email)) {
            echo 'Khong lay duoc email tu Google.';
            return;
        }

        $account = $this->accountModel->getAccountByGoogleId($googleId);

        if (!$account) {
            $account = $this->accountModel->getAccountByEmail($email);

            if ($account) {
                $this->accountModel->linkGoogleAccount($account->id, $googleId, $avatar);
                $account = $this->accountModel->getAccountByGoogleId($googleId);
            }
        }

        if (!$account) {
            $this->accountModel->saveGoogleAccount(
                $googleId,
                'google_' . $googleId,
                $email,
                $fullname,
                $avatar
            );

            $account = $this->accountModel->getAccountByGoogleId($googleId);
        }

        if (!$account) {
            echo 'Khong tao hoac lien ket duoc tai khoan Google.';
            return;
        }

        $_SESSION['username'] = $account->username;
        $_SESSION['role'] = $account->role;

        header('Location: /Product');
        exit;
    }

    public function github()
    {
        $params = [
            'client_id' => $this->githubConfig['client_id'],
            'redirect_uri' => $this->githubConfig['redirect_uri'],
            'scope' => 'user:email',
        ];

        header('Location: https://github.com/login/oauth/authorize?' . http_build_query($params));
        exit;
    }

    public function githubCallback()
    {
        $code = $_GET['code'] ?? '';

        if ($code === '') {
            echo 'GitHub khong tra ve code.';
            return;
        }

        $tokenResponse = $this->postForm('https://github.com/login/oauth/access_token', [
            'client_id' => $this->githubConfig['client_id'],
            'client_secret' => $this->githubConfig['client_secret'],
            'code' => $code,
            'redirect_uri' => $this->githubConfig['redirect_uri'],
        ]);

        $accessToken = $tokenResponse['access_token'] ?? '';

        if ($accessToken === '') {
            echo 'Khong lay duoc access token tu GitHub.';
            return;
        }

        $githubUser = $this->getJson('https://api.github.com/user', $accessToken);

        $githubId = (string) ($githubUser['id'] ?? '');
        $username = $githubUser['login'] ?? '';
        $fullname = $githubUser['name'] ?? $username;
        $avatar = $githubUser['avatar_url'] ?? '';
        $email = $githubUser['email'] ?? null;

        if ($email === null) {
            $emails = $this->getJson('https://api.github.com/user/emails', $accessToken);

            foreach ($emails as $item) {
                if (!empty($item['primary']) && !empty($item['verified'])) {
                    $email = $item['email'];
                    break;
                }
            }
        }

        if ($githubId === '' || $username === '') {
            echo 'Khong lay duoc thong tin GitHub.';
            return;
        }

        $account = $this->accountModel->getAccountByGithubId($githubId);

        if (!$account && $email) {
            $account = $this->accountModel->getAccountByEmail($email);

            if ($account) {
                $this->accountModel->linkGithubAccount($account->id, $githubId, $avatar);
                $account = $this->accountModel->getAccountByGithubId($githubId);
            }
        }

        if (!$account) {
            $this->accountModel->saveGithubAccount(
                $githubId,
                'github_' . $username,
                $email,
                $fullname,
                $avatar
            );

            $account = $this->accountModel->getAccountByGithubId($githubId);
        }

        if (!$account) {
            echo 'Khong tao hoac lien ket duoc tai khoan GitHub.';
            return;
        }

        $_SESSION['username'] = $account->username;
        $_SESSION['role'] = $account->role;

        header('Location: /Product');
        exit;
    }

    private function postForm($url, array $data)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true) ?? [];
    }

    private function getJson($url, $accessToken)
    {
        $curl = curl_init($url);

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: MyStoreApp',
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true) ?? [];
    }

    public function register()
    {
        include 'app/views/account/register.php';
    }

    public function login()
    {
        include 'app/views/account/login.php';
    }

    public function save()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Account/register');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $fullname = trim($_POST['fullname'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirmpassword'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $email = trim($_POST['email'] ?? '');

        $errors = [];

        if ($username === '') $errors['username'] = 'Vui long nhap username.';
        if ($fullname === '') $errors['fullname'] = 'Vui long nhap ho ten.';
        if ($password === '') $errors['password'] = 'Vui long nhap mat khau.';
        if ($password !== $confirmPassword) $errors['confirm'] = 'Mat khau xac nhan khong khop.';
        if ($email === '') { $errors['email'] = 'Vui long nhap email.';
        }

        if ($email !== '' && $this->accountModel->getAccountByEmail($email)) {
            $errors['email_exists'] = 'Email nay da duoc su dung.';
        }

        if (!in_array($role, ['admin', 'user'])) {
            $role = 'user';
        }

        if ($this->accountModel->getAccountByUsername($username)) {
            $errors['account'] = 'Tai khoan nay da ton tai.';
        }

        if (!empty($errors)) {
            include 'app/views/account/register.php';
            return;
        }

        if ($this->accountModel->save($username, $email, $fullname, $password, $role)) {
            header('Location: /Account/login');
            exit;
        }

        $errors['save'] = 'Khong tao duoc tai khoan.';
        include 'app/views/account/register.php';
    }

    public function checkLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /Account/login');
            exit;
        }

        $login = trim($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $account = $this->accountModel->getAccountByEmail($login);
        } else {
            $account = $this->accountModel->getAccountByUsername($login);
        }

        if ($account && password_verify($password, $account->password)) {
            $_SESSION['username'] = $account->username;
            $_SESSION['role'] = $account->role;

            header('Location: /Product');
            exit;
        }

        $error = $account ? 'Mat khau khong dung.' : 'Khong tim thay tai khoan.';
        include 'app/views/account/login.php';
    }

    public function logout()
    {
        unset($_SESSION['username']);
        unset($_SESSION['role']);

        header('Location: /Product');
        exit;
    }
}
