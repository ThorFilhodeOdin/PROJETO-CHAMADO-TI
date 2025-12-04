<?php
class Database
{
//Só o Database protege contra SQL Injection
    private $pdo;


  
    private $host = 'localhost';
    private $dbname = 'chamados';
    private $username = 'userAdm';
    private $password = 'secsistemas37666451829369Rr@';
    



    //BANCO LOCAL
    public function __construct()
    {
        try {
            $this->pdo = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
        } catch (PDOException $e) {
            die("Erro MySQL: " . $e->getMessage());
        }
    }




    private function escapeTable($table)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }

    private function escapeColumn($column)
    {
        return '`' . str_replace('`', '``', $column) . '`';
    }




    public function insert($table, $data)
    {
        $table = $this->escapeTable($table);
        $columns = implode(', ', array_map([$this, 'escapeColumn'], array_keys($data)));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO `$table` ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }







    public function update(string $table, array $data, array $conditions): bool
    {
        try {
            $table = $this->escapeTable($table);
            $whereParts = [];
            $params = [];
            $setParts = [];
            foreach ($data as $key => $value) {
                $setParts[] = "`$key` = :$key";
                $params[":$key"] = $value;
            }
            foreach ($conditions as $key => $value) {
                if (strpos($key, 'id') !== false || strpos($key, 'Id') !== false) {
                    $value = $value;
                }
                $whereParts[] = "`$key` = :where_$key";
                $params[":where_$key"] = $value;
            }

            $sql = "UPDATE `$table` SET " . implode(', ', $setParts) .
                " WHERE " . implode(' AND ', $whereParts);

            $stmt = $this->pdo->prepare($sql);

            foreach ($data as $key => $value) {
                if (is_null($value) || $value === 'null' || $value === '') {
                    $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                } elseif (is_string($value) && strlen($value) > 1000) {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_LOB);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }

            foreach ($conditions as $key => $value) {
                $stmt->bindValue(":where_$key", $value);
            }

            $result = $stmt->execute();

            return $result;
        } catch (PDOException $e) {
            error_log("Erro Database update: " . $e->getMessage());
            error_log("SQL Error Info: " . print_r($this->pdo->errorInfo(), true));
            return false;
        }
    }



    public function selectx(string $table, string $field, array $conditions): ?string
    {
        $table = $this->escapeTable($table);
        $field = $this->escapeColumn($field);
        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $escapedKey = $this->escapeColumn($key); 
            $where[] = "$escapedKey = :$key";
            $params[":$key"] = $value;
        }

        $sql = "SELECT $field FROM `$table` WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result[$field] ?? null;
    }



    public function select($table, $conditions = [], $limit = null)
    {
        $table = $this->escapeTable($table);
        $sql = "SELECT * FROM `$table`";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $escapedKey = $this->escapeColumn($key);
                $where[] = "$escapedKey = :$key";
                $params[":$key"] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($limit) {
            $limit = (int)$limit;
            $sql .= " LIMIT $limit";
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function delete($table, $conditions)
    {
        $table = $this->escapeTable($table);
        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $escapedKey = $this->escapeColumn($key); 
            $where[] = "$escapedKey = :$key";
            $params[":$key"] = $value;
        }
        $sql = "DELETE FROM `$table` WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }











    /* 

    ANTIGOS METODOS DATABSE- CONSIDDERADO INSEGURO
    public function insert($table, $data){ //funciona para qualquer tabela e qualquer conjunto de campos
      $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
       $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data); //retorna valor booleano
  }

  


public function update(string $table, array $data, array $conditions): bool{
    try {
        error_log("Database  update chamado");
        
        $whereParts = [];
        $params = [];
        $setParts = [];

        foreach ($data as $key => $value) {
            $setParts[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        foreach ($conditions as $key => $value) {
            if (strpos($key, 'id') !== false || strpos($key, 'Id') !== false) {
                $value = (int) $value;
            }
            $whereParts[] = "`$key` = :where_$key";
            $params[":where_$key"] = $value;
        }

        $sql = "UPDATE `$table` SET " . implode(', ', $setParts) .
               " WHERE " . implode(' AND ', $whereParts);

        error_log("SQL: $sql");
        error_log("Parâmetros: " . print_r($params, true));

        $stmt = $this->pdo->prepare($sql);

        foreach ($data as $key => $value) {
            if (is_null($value) || $value === 'null' || $value === '') {
                $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                error_log("Bind NULL para campo: $key");
            } elseif (is_string($value) && strlen($value) > 1000) {
                $stmt->bindValue(":$key", $value, PDO::PARAM_LOB);
                error_log("Bind BLOB para campo: $key (tamanho: " . strlen($value) . ")");
            } else {
                $stmt->bindValue(":$key", $value);
                error_log("Bind normal para campo: $key = $value");
            }
        }

        foreach ($conditions as $key => $value) {
            $stmt->bindValue(":where_$key", $value);
            error_log("Bind WHERE: :where_$key = $value");
        }

        $result = $stmt->execute();
        error_log("Resultado execute: " . ($result ? 'true' : 'false'));
        error_log("Linhas afetadas: " . $stmt->rowCount());

        return $result;

    } catch (PDOException $e) {
        error_log("Erro Database update: " . $e->getMessage());
        error_log("SQL Error Info: " . print_r($this->pdo->errorInfo(), true));
        return false;
    }
}




    public function selectx(string $table, string $field, array $conditions): ?string{//select x
        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = :$key";
            $params[":$key"] = $value;
        }

        $sql = "SELECT $field FROM $table WHERE " . implode(' AND ', $where) . " LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result[$field] ?? null;
    }



    
    public function select($table, $conditions = [], $limit = null){//select *
        $sql = "SELECT * FROM $table";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";      
                $params[":$key"] = $value;        
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($limit) {
            $sql .= " LIMIT $limit";  
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);      
        return $stmt->fetchAll();    
    }





    public function delete($table, $conditions){
        $where = [];
        $params = [];
        foreach ($conditions as $key => $value) {
            $where[] = "$key = :$key";
            $params[":$key"] = $value;
        }
        $sql = "DELETE FROM $table WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
*/
}
