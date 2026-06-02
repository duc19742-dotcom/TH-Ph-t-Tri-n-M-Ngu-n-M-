<?php

class AccountModel
{
    private $conn;
    private $table_name = "account";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function getAccountByUsername($username)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function save($username, $email, $fullname, $password, $role = 'user')
    {
        if ($this->getAccountByUsername($username) || $this->getAccountByEmail($email)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table_name . "
                  (username, email, fullname, password, role)
                  VALUES (:username, :email, :fullname, :password, :role)";

        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($username));
        $fullname = htmlspecialchars(strip_tags($fullname));
        $password = password_hash($password, PASSWORD_BCRYPT);
        $role = htmlspecialchars(strip_tags($role));
        $email = htmlspecialchars(strip_tags($email));


        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $email);


        return $stmt->execute();
    }

    public function getAccountByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getAccountByGithubId($githubId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE github_id = :github_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':github_id', $githubId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function linkGithubAccount($accountId, $githubId, $avatar)
    {
        $query = "UPDATE " . $this->table_name . "
                SET github_id = :github_id, avatar = :avatar
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':github_id', $githubId);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':id', $accountId);

        return $stmt->execute();
    }

    public function getAccountByGoogleId($googleId)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE google_id = :google_id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':google_id', $googleId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function linkGoogleAccount($accountId, $googleId, $avatar)
    {
        $query = "UPDATE " . $this->table_name . "
                SET google_id = :google_id, avatar = :avatar
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':google_id', $googleId);
        $stmt->bindParam(':avatar', $avatar);
        $stmt->bindParam(':id', $accountId);

        return $stmt->execute();
    }

    public function saveGoogleAccount($googleId, $username, $email, $fullname, $avatar)
    {
        $query = "INSERT INTO " . $this->table_name . "
                (google_id, username, email, fullname, password, role, avatar)
                VALUES (:google_id, :username, :email, :fullname, '', 'user', :avatar)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':google_id', $googleId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':avatar', $avatar);

        return $stmt->execute();
    }

    public function saveGithubAccount($githubId, $username, $email, $fullname, $avatar)
    {
        $query = "INSERT INTO " . $this->table_name . "
                (github_id, username, email, fullname, password, role, avatar)
                VALUES (:github_id, :username, :email, :fullname, '', 'user', :avatar)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':github_id', $githubId);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':fullname', $fullname);
        $stmt->bindParam(':avatar', $avatar);

        return $stmt->execute();
    }
}
