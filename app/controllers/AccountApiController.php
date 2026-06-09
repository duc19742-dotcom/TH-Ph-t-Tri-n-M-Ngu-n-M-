<?php

require_once 'app/config/database.php';
require_once 'app/models/AccountModel.php';
require_once 'app/utils/JWTHandler.php';

class AccountApiController
{
    private $accountModel;
    private $jwtHandler;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->accountModel = new AccountModel($db);
        $this->jwtHandler = new JWTHandler();
    }

    public function store()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $login = trim($data['login'] ?? $data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            $account = $this->accountModel->getAccountByEmail($login);
        } else {
            $account = $this->accountModel->getAccountByUsername($login);
        }

        if (!$account || !password_verify($password, $account->password)) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid credentials']);
            return;
        }

        $_SESSION['username'] = $account->username;
        $_SESSION['role'] = $account->role;

        $token = $this->jwtHandler->encode([
            'id' => $account->id,
            'username' => $account->username,
            'role' => $account->role,
        ]);

        echo json_encode([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $account->id,
                'username' => $account->username,
                'role' => $account->role,
            ],
        ]);
    }
}

?>
