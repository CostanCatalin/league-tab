<?php
namespace LeagueTab;


class Users extends DatabaseConnection{

    public function insertUser($server_id, $username, $cookie) {
        return $this->queryList('insert into users values (:server_id, :username, :cookie)', [':username' => $username, ':server_id' => $server_id, ':cookie' => $cookie], true);
    }

    public function getUserByUsername($username) {
        return $this->queryList('Select * from users where username = :username', [':username' => $username], true);
    }

    public function getUserByCookie($cookie) {
        return $this->queryList('Select * from users where cookie = :cookie', [':cookie' => $cookie], true);
    }
}