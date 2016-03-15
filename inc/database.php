<?php

class pdodb
{

    private $db;

    private function dbExists()
    {
        $showquery = "SHOW DATABASES LIKE '" . DBASE . "'";
        $showresult = $this->db->query($showquery);
        return (boolean)($showresult->fetch());
    }

    public function __construct()
    {
        $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        try {
            $this->db = new PDO(DSN, USERNAME, PASSWORD, $option);
        } catch (PDOException $failure) {
            echo 'Connection failed: ' . $failure->getMessage();
        }
        if (!pdodb::dbExists()) {
            $this->db->query("CREATE DATABASE" . DBASE);
        }
        $this->db->query("USE " . DBASE);
        try {
            $this->db->query(CREATEQUERY);
        } catch (PDOException $failure) {
            echo 'Server failed: ' . $failure->getMessage();
        }
    }

    public function query($sql, $bindings = null)
    {
        try {
            if (isset($bindings)) {
                $results = $this->db->prepare($sql);
                $results->execute($bindings);
            }
            else {
                $results = $this->db->query($sql);
            }


            if (strpos($sql, "SELECT") !== false) {
                return $results->fetchAll(PDO::FETCH_ASSOC);
            }

            return $results->rowCount();

        } catch (PDOException $failure) {
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