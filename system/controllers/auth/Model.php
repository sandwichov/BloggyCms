<?php
class User implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getById',
        'getByUsername',
        'authenticate',
        'authenticateByEmail',
        'getTotalCount',
        'getActiveUsers',
        'updatePassword'
    ];

    private $db;

    /**
    * Конструктор класса пользователя
    * Инициализирует объект с подключением к базе данных
    *
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
    * Аутентификация пользователя по имени и паролю
    * Проверяет соответствие введенного пароля хешу в базе данных
    * и преобразует поле is_admin в булево значение
    *
    * @param string $username Имя пользователя
    * @param string $password Пароль в открытом виде
    * @return array|false Массив данных пользователя при успехе, false при неудаче
    */
    public function authenticate($username, $password) {
        $user = $this->db->fetch("SELECT * FROM users WHERE username = ?", [$username]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        $user['is_admin'] = (bool)($user['is_admin'] ?? false);
        
        return $user;
    }

    /**
    * Получение пользователя по ID
    * Возвращает полную информацию о пользователе с предустановленными
    * значениями для опциональных полей (NULL заменяется на значения по умолчанию)
    *
    * @param int $id Идентификатор пользователя
    * @return array|null Массив данных пользователя или null если не найден
    */
    public function getById($id) {
        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
        
        if ($user) {
            // Заменяем NULL значения на пустые строки для отображения в формах
            $user['display_name'] = $user['display_name'] ?? '';
            $user['bio'] = $user['bio'] ?? '';
            $user['website'] = $user['website'] ?? '';
            $user['avatar'] = $user['avatar'] ?? 'default.jpg';
            $user['role'] = $user['role'] ?? 'user';
            $user['status'] = $user['status'] ?? 'active';
        }
        
        return $user;
    }

    /**
    * Обновление пароля пользователя с проверкой текущего
    * Выполняет верификацию старого пароля перед установкой нового,
    * хешируя новый пароль алгоритмом PASSWORD_DEFAULT
    *
    * @param int $id Идентификатор пользователя
    * @param string $currentPassword Текущий пароль для проверки
    * @param string $newPassword Новый пароль
    * @return bool true при успешном обновлении, false при ошибке верификации
    */
    public function updatePassword($id, $currentPassword, $newPassword) {
        $user = $this->getById($id);
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return false;
        }
        
        return $this->update($id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }

    // Методы для админки

    /**
    * Получение всех пользователей с фильтрацией по роли и статусу
    * Поддерживает фильтрацию по роли (role) и статусу (active/inactive)
    * Результаты сортируются по дате создания (новые выше)
    *
    * @param string|null $role Фильтр по роли (admin/user/moderator и т.д.)
    * @param string|null $status Фильтр по статусу (active/inactive)
    * @return array Массив пользователей, удовлетворяющих фильтрам
    */
    public function getAllWithFilters($role = null, $status = null) {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];

        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
    * Поиск пользователя по имени пользователя
    * Используется для проверки уникальности username при регистрации
    * и для поиска пользователей в административной панели
    *
    * @param string $username Имя пользователя для поиска
    * @return array|null Данные пользователя или null если не найден
    */
    public function getByUsername($username) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE username = ?",
            [$username]
        );
    }

    /**
    * Поиск пользователя по email адресу
    * Используется для проверки уникальности email при регистрации,
    * восстановления пароля и поиска в административной панели
    *
    * @param string $email Email адрес для поиска
    * @return array|null Данные пользователя или null если не найден
    */
    public function getByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }

    /**
    * Создание нового пользователя в базе данных
    * Принимает ассоциативный массив данных и динамически
    * формирует SQL запрос на основе переданных полей
    *
    * @param array $data Ассоциативный массив данных пользователя
    *                    (например: ['username' => 'john', 'email' => 'john@example.com'])
    * @return int ID созданного пользователя
    * @throws Exception При ошибке выполнения SQL запроса
    */
    public function create($data) {
        $fields = [];
        $placeholders = [];
        $values = [];

        foreach ($data as $field => $value) {
            $fields[] = $field;
            $placeholders[] = '?';
            $values[] = $value;
        }

        $sql = "INSERT INTO users (" . implode(', ', $fields) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, $values);
        return $this->db->lastInsertId();
    }

    /**
    * Обновление данных пользователя
    * Универсальный метод, поддерживающий два способа обновления:
    * 1. Через метод update() Database класса (если доступен)
    * 2. Через прямой SQL запрос (режим совместимости)
    * Автоматически фильтрует поля для предотвращения SQL инъекций
    *
    * @param int $id Идентификатор пользователя
    * @param array $data Ассоциативный массив обновляемых полей и значений
    * @return bool|int true/количество обновленных строк при успехе, false при ошибке
    */
    public function update($id, $data) {
        // Если используется метод update из Database класса
        if (method_exists($this->db, 'update')) {
            $validFields = ['display_name', 'email', 'website', 'bio', 'avatar', 'password', 'username', 'role', 'status'];
            $filteredData = array_intersect_key($data, array_flip($validFields));
            
            return $this->db->update('users', $filteredData, ['id' => $id]);
        } else {
            // Альтернативная реализация для обратной совместимости
            $fields = [];
            $values = [];

            foreach ($data as $field => $value) {
                $fields[] = "{$field} = ?";
                $values[] = $value;
            }

            $values[] = $id;

            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
            return $this->db->query($sql, $values);
        }
    }

    /**
    * Удаление пользователя из базы данных
    * Выполняет мягкое или физическое удаление в зависимости от структуры БД
    * Перед вызовом рекомендуется проверять зависимости (посты, комментарии)
    *
    * @param int $id Идентификатор удаляемого пользователя
    * @return bool|int true/количество удаленных строк при успехе
    * @throws Exception При ошибке выполнения SQL запроса
    */
    public function delete($id) {
        return $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
    }

    // Дополнительные методы для удобства
    
    /**
    * Получение общего количества пользователей в системе
    * Используется для статистики и пагинации в административной панели
    *
    * @return int Количество пользователей в базе данных
    */
    public function getTotalCount() {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM users");
        return $result['count'] ?? 0;
    }
    
    /**
    * Получение списка администраторов системы
    * Возвращает всех пользователей с ролью 'admin',
    * отсортированных по имени пользователя
    *
    * @return array Массив администраторов
    */
    public function getAdmins() {
        return $this->db->fetchAll("SELECT * FROM users WHERE role = 'admin' ORDER BY username");
    }
    
    /**
    * Получение списка активных пользователей
    * Возвращает пользователей со статусом 'active',
    * отсортированных по дате регистрации (новые выше)
    * Используется для отображения в административной панели
    *
    * @return array Массив активных пользователей
    */
    public function getActiveUsers() {
        return $this->db->fetchAll("
            SELECT * FROM users 
            WHERE status = 'active' 
            ORDER BY created_at DESC
        ");
    }

    /**
    * Аутентификация пользователя по email и паролю
    * Альтернативный метод входа для систем, поддерживающих вход по email
    * Проверяет соответствие пароля хешу в базе данных
    *
    * @param string $email Email пользователя
    * @param string $password Пароль в открытом виде
    * @return array|false Массив данных пользователя при успехе, false при неудаче
    */
    public function authenticateByEmail($email, $password) {
        $user = $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }

}