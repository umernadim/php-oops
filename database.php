<?php

class Database
{
    private $db_host = "localhost";
    private $db_user = "root";
    private $db_password = "";
    private $db_name = "rest-api";

    private $mysqli = "";
    private $result = array();
    private $conn = false;
    public function __construct()
    {
        if (!$this->conn) {
            $this->mysqli = new mysqli($this->db_host, $this->db_user, $this->db_password, $this->db_name);
            $this->conn = true;

            if ($this->mysqli->connect_error) {
                array_push($this->result, $this->mysqli->connect_error);
                return false;
            }
        } else {
            return true;
        }
    }


    public function insert($table, $params = array())
    {
        if ($this->tableExist($table)) {
            $table_columns = implode(', ', array_keys($params));
            $table_vlaues = implode("', '", $params);
            $sql = "INSERT INTO $table ($table_columns) VALUES('$table_vlaues')";
            if ($this->mysqli->query($sql)) {
                array_push($this->result, $this->mysqli->insert_id);
                return true;
            } else {
                array_push($this->result, $this->mysqli->error);
                return false;
            }
        } else {
            return false;
        }
    }
    public function update($table, $params = array(), $where = null)
    {
        if ($this->tableExist($table)) {
            $args = array();
            foreach ($params as $key => $value) {
                $args[] = "$key = '$value'";
            }
            $sql = "UPDATE $table SET " . implode(', ', $args);
            if ($where != null) {
                $sql .= "WHERE $where";
            }
            if ($this->mysqli->query($sql)) {
                array_push($this->result, $this->mysqli->affected_rows);
                return true;
            } else {
                array_push($this->result, $this->mysqli->error);
            }
        } else {
            return false;
        }
    }
    public function delete($table, $where = null)
    {
        if ($this->tableExist($table)) {
            $sql = "DELETE FROM $table";
            if ($where != null) {
                $sql .= " WHERE $where";
            }
            if ($this->mysqli->query($sql)) {
                array_push($this->result, $this->mysqli->affected_rows);
                return true;
            } else {
                array_push($this->result, $this->mysqli->error);
                return false;
            }
        } else {
            return false;
        }
    }
    public function select($table, $rows = "*", $join = null, $where = null, $order = null, $limit = null)
    {
        if ($this->tableExist($table)) {
            $sql = "SELECT $rows FROM $table";
            if ($join != null) {
                $sql .= "JOIN $join";
            }
            if ($where != null) {
                $sql .= "WHERE $where";
            }
            if ($order != null) {
                $sql .= "ORDER BY $order";
            }
            if ($limit != null) {
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                } else {
                    $page = 1;
                }
                $start = ($page - 1) * $limit;
                $sql .= "LIMIT $start, $limit";
            }

            $query = $this->mysqli->query($sql);
            if ($query) {
                $this->result = $query->fetch_all(MYSQLI_ASSOC);
                return true;
            } else {
                array_push($this->result, $this->mysqli->error);
                return false;
            }
        } else {
            return false;
        }
    }

    public function pagination($table, $join = null, $where = null, $limit = null)
    {
        if ($this->tableExist($table)) {
            if ($join != null) {
                $sql = "SELECT COUNT(*) FROM $table";

                if ($join != null) {
                    $sql .= " JOIN $join";
                }
                if ($where != null) {
                    $sql .= " WHERE $where";
                }

                $query = $this->mysqli->query($sql);
                $total_records = $query->fetch_array()[0];
                $total_page = ceil($total_records / $limit);
                $url = basename($_SERVER['PHP_SELF']);

                $page = isset($_GET['page']) ? $_GET['page'] : 1;

                $output = "<ul class='pagination'>";
                if ($page > 1) {
                    $output .= "<li><a href='$url?page=" . ($page - 1) . "'>Prev</a></li>";
                }
                if ($total_records > $limit) {
                    for ($i = 1; $i <= $total_page; $i++) {
                        $cls = ($i == $page) ? "class='active'" : "";
                        $output .= "<li $cls><a href='$url?page=$i'>$i</a></li>";
                    }
                }
                if ($total_page < $page) {
                    $output .= "<li><a href='$url?page=" . ($page + 1) . "'>Next</a></li>";
                    $output .= "</ul>";
                    echo $output;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function sql($sql)
    {
        $query = $this->mysqli->query($sql);
        if ($query) {
            $this->result = $query->fetch_all(MYSQLI_ASSOC);
            return true;
        } else {
            array_push($this->result, $this->mysqli->error);
            return false;
        }
    }

    private function tableExist($table)
    {
        $sql = "SHOW TABLES FROM `{$this->db_name}` LIKE '$table'";
        $tableInDb = $this->mysqli->query($sql);
        if ($tableInDb) {
            if ($tableInDb->num_rows == 1) {
                return true;
            } else {
                array_push($this->result, $table . " does not exist.");
                return false;
            }
        }
    }

    public function getResult()
    {
        $val = $this->result;
        $this->result = array();
        return $val;
    }
    public function __destruct()
    {
        if ($this->conn) {
            if ($this->mysqli->close()) {
                $this->conn = false;
                return true;
            }
        } else {
            return false;
        }
    }
}
