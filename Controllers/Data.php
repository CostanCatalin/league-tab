<?php
namespace LeagueTab;


class Data extends DatabaseConnection{
    
    public function insertData($user_id, $data) {
        return $this->queryList('insert into data (user_id, data) values (:user_id, :data)', [':user_id' => $user_id, ':data' => json_encode($data)], true);
    }

    public function updateData($id, $data) {
        return $this->queryList('update data set data = :data where id = :id', [':data' => json_encode($data), ':id' => $id], true);
    }

    public function getDataByUserId($user_id) {
        return $this->queryList('Select * from data where user_id = :user_id', [':user_id' => $user_id], true);
    }
}