<?php
//a class which connects to a database to send and receeive data using variables defined in connectection.php file
//a reuseable file for other projects
class pdodb
{
    private $db;

    public function __construct()
    {
        $dsn = "mysql:host=" . IP . ";charset-UTF-8";
        $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        try {
            $this->db = new PDO($dsn, USERNAME, PASSWORD, $option);
        } catch (PDOException $failure) {
            echo 'Connection failed: ' . $failure->getMessage();
        }
        if (!pdodb::dbExists()) {
            $this->db->query("CREATE DATABASE " . DATABASENAME);
        }
        $this->db->query("USE " . DATABASENAME);
        try {
            $this->db->query(CREATEQUERY);
        } catch (PDOException $failure) {
            echo 'Server failed: ' . $failure->getMessage();
        }
    }

    private function dbExists()
    {
        $showQuery = "SHOW DATABASES LIKE '" . DATABASENAME . "'";
        $showResult = $this->db->query($showQuery);
        return (boolean)($showResult->fetch());
    }

    public function query($sql, $bindings = null)
    {
        try {
            if (isset($bindings)) {
                $results = $this->db->prepare($sql);
                $results->execute($bindings);
            } else {
                $results = $this->db->query($sql);
            }

            if (strpos($sql, "SELECT") !== false) {
                return $results->fetchAll(PDO::FETCH_ASSOC);
            }

            return $results->rowCount();

        } catch (PDOException $failure) {
            $results = [];
            $results["meta"]["ok"] = false;
            $results["meta"]["exception"] = $failure;

            return $results;
        }
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }
}