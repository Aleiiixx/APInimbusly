<?php

class DB {

    private $pdo;

    public function connect() {
        
        global $env;

        try {
            $host = $env['DB_HOST'];
            $username = $env['DB_USERNAME'];
            $password = $env['DB_PASSWORD'];
            $database = $env['DB_DATABASE'];

            $dsn = "mysql:host=$host;dbname=$database";
            $this->pdo = new PDO($dsn, $username, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->pdo;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
