<?php

/**
 * Класс для работы с базой данных (Singleton)
 */
class Database {
    /**
     * @var PDO Подключение к базе данных
     */
    private $connection;
    
    /**
     * @var self|null Единственный экземпляр класса
     */
    private static $instance = null;

    /**
     * Конструктор Database
     * Создает подключение к базе данных с кодировкой utf8mb4
     */
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"]
            );
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->connection->exec("SET CHARACTER SET utf8mb4");
            $this->connection->exec("SET character_set_connection = utf8mb4");
            $this->connection->exec("SET character_set_results = utf8mb4");
            $this->connection->exec("SET character_set_client = utf8mb4");
            
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Получить экземпляр базы данных (Singleton)
     *
     * @return self Экземпляр Database
     */
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Выполняет SQL запрос с параметрами
     *
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return PDOStatement Результат выполнения
     */
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        
        foreach ($params as $key => $value) {
            $paramType = PDO::PARAM_STR;
            
            if (is_int($value)) {
                $paramType = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $paramType = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $paramType = PDO::PARAM_NULL;
            }
            
            if (is_int($key)) {
                $stmt->bindValue($key + 1, $value, $paramType);
            } else {
                $stmt->bindValue($key, $value, $paramType);
            }
        }
        
        $stmt->execute();
        return $stmt;
    }

    /**
     * Получает одну строку результата
     *
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return array|null Ассоциативный массив или null
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Получает все строки результата
     *
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return array Массив ассоциативных массивов
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Получает ID последней вставленной записи
     *
     * @return string ID последней вставленной записи
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Обновляет записи в таблице
     *
     * @param string $table Имя таблицы
     * @param array $data Данные для обновления
     * @param array $where Условия WHERE
     * @return int Количество обновленных строк
     */
    public function update($table, $data, $where) {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        $whereParts = [];
        foreach ($where as $field => $value) {
            $whereParts[] = "$field = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE $table SET " . implode(', ', $fields);
        if (!empty($whereParts)) {
            $sql .= " WHERE " . implode(' AND ', $whereParts);
        }
        
        return $this->query($sql, $values)->rowCount();
    }

    /**
     * Начинает транзакцию
     *
     * @return bool Результат начала транзакции
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Подтверждает транзакцию
     *
     * @return bool Результат подтверждения
     */
    public function commit() {
        return $this->connection->commit();
    }
    
    /**
     * Откатывает транзакцию
     *
     * @return bool Результат отката
     */
    public function rollBack() {
        return $this->connection->rollBack();
    }

    /**
     * Вставляет запись в таблицу
     *
     * @param string $table Имя таблицы
     * @param array $data Данные для вставки
     * @return PDOStatement Результат выполнения
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        return $this->query($sql, $values);
    }

    /**
     * Удаляет записи из таблицы
     *
     * @param string $table Имя таблицы
     * @param array $conditions Условия удаления
     * @return PDOStatement Результат выполнения
     */
    public function delete($table, $conditions) {
        $whereParts = [];
        $values = [];
        
        foreach ($conditions as $column => $value) {
            $whereParts[] = "{$column} = ?";
            $values[] = $value;
        }
        
        $sql = "DELETE FROM {$table} WHERE " . implode(' AND ', $whereParts);
        
        return $this->query($sql, $values);
    }

    /**
     * Получить одно значение из первой строки результата
     *
     * @param string $sql SQL запрос
     * @param array $params Параметры запроса
     * @return mixed Значение или null
     */
    public function fetchValue($sql, $params = []) {
        $result = $this->fetch($sql, $params);
        if ($result) {
            return reset($result);
        }
        return null;
    }

    /**
     * Проверяет поддержку utf8mb4 в базе данных
     *
     * @return bool Поддерживает ли база utf8mb4
     */
    public function checkUtf8mb4Support() {
        try {
            $result = $this->fetch("SHOW VARIABLES LIKE 'character_set_database'");
            $charset = $result['Value'] ?? '';
            return stripos($charset, 'utf8mb4') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Проверяет кодировку таблицы
     *
     * @param string $tableName Имя таблицы
     * @return string|null Кодировка таблицы
     */
    public function getTableCharset($tableName) {
        try {
            $result = $this->fetch("SHOW CREATE TABLE {$tableName}");
            $createTable = $result['Create Table'] ?? '';
            
            if (preg_match('/CHARSET=([a-zA-Z0-9_]+)/', $createTable, $matches)) {
                return $matches[1];
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Изменяет кодировку таблицы на utf8mb4
     *
     * @param string $tableName Имя таблицы
     * @return bool|PDOStatement Результат выполнения
     */
    public function convertTableToUtf8mb4($tableName) {
        try {
            $sql = "ALTER TABLE {$tableName} 
                    CONVERT TO CHARACTER SET utf8mb4 
                    COLLATE utf8mb4_unicode_ci";
            
            return $this->query($sql);
        } catch (\Exception $e) {
            return false;
        }
    }
}