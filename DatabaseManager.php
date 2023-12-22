<?php

class DatabaseManager
{
    private $connection;

    public function __construct()
    {
        // 在这里建立数据库连接
        $host = 'localhost';
        $dbname = 'consultation';
        $username = 'root';
        $password = '123456';

        $this->connection = new PDO('mysql:host=' . $host . ';dbname=' . $dbname, $username, $password);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getAll($table)
    {
        $stmt = $this->connection->prepare("SELECT * FROM $table");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function get($table, $id)
    {
        $stmt = $this->connection->prepare("SELECT * FROM $table WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = $this->connection->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        return $this->connection->lastInsertId();
    }

    public function update($table, $id, $data)
    {
        $setSql = '';
        foreach ($data as $key => $value) {
            $setSql .= "$key = :$key, ";
        }
        $setSql = rtrim($setSql, ', ');

        $sql = "UPDATE $table SET $setSql WHERE id = :id";
        $data['id'] = $id;

        $stmt = $this->connection->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        $stmt->execute();

        return $stmt->rowCount();
    }

    public function delete($table, $id)
    {
        $sql = "DELETE FROM $table WHERE id = :id";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->rowCount();
    }
}