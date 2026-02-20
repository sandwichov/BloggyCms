<?php

/**
 * Модель меню
 * Управляет созданием, хранением и извлечением меню навигации сайта
 * Поддерживает древовидную структуру пунктов меню и управление правами доступа
 * 
 * @package models
 */
class MenuModel {
    
    /**
     * @var Database Объект подключения к базе данных
     */
    private $db;
    
    /**
     * Конструктор модели меню
     * Инициализирует подключение к базе данных
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Создание нового меню
     * Добавляет запись меню в базу данных
     *
     * @param array $data Массив данных меню:
     * - name: название меню
     * - description: описание меню
     * - template: шаблон меню
     * - structure: структура меню в JSON
     * - status: статус меню (active/inactive)
     * @return int ID созданного меню
     */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->insert('menus', $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Обновление существующего меню
     * Изменяет данные меню в базе данных
     *
     * @param int $id ID обновляемого меню
     * @param array $data Массив данных для обновления
     * @return bool Результат выполнения операции
     */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->db->update('menus', $data, ['id' => $id]) > 0;
    }
    
    /**
     * Удаление меню
     * Удаляет меню из базы данных по его ID
     *
     * @param int $id ID удаляемого меню
     * @return bool Результат выполнения операции
     */
    public function delete($id) {
        return $this->db->delete('menus', ['id' => $id]) > 0;
    }
    
    /**
     * Получение меню по ID
     * Возвращает данные меню по его идентификатору
     *
     * @param int $id ID меню
     * @return array|null Данные меню или null если не найдено
     */
    public function getById($id) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE id = ?", 
            [(int)$id]
        );
    }
    
    /**
     * Получение всех меню
     * Возвращает список всех меню в системе отсортированных по дате создания
     *
     * @return array Массив всех меню
     */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT * FROM menus ORDER BY created_at DESC"
        );
    }
    
    /**
     * Получение меню по названию
     * Возвращает данные меню по его названию
     *
     * @param string $name Название меню
     * @return array|null Данные меню или null если не найдено
     */
    public function getByName($name) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE name = ?", 
            [$name]
        );
    }
    
    /**
     * Получение активного меню для указанного шаблона
     * Возвращает активное меню, связанное с конкретным шаблоном
     *
     * @param string $template Название шаблона
     * @return array|null Данные меню или null если не найдено
     */
    public function getByTemplate($template) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE template = ? AND status = 'active'", 
            [$template]
        );
    }
    
    /**
     * Получение всех доступных шаблонов меню из папки текущей темы
     * Сканирует директорию шаблонов меню текущей темы
     *
     * @return array Ассоциативный массив доступных шаблонов меню
     */
    public function getAvailableTemplates() {
        $templates = [];
        $currentTheme = $this->getCurrentTheme();
        
        $menuTemplatesPath = TEMPLATES_PATH . '/' . $currentTheme . '/front/assets/menu';
        
        // Проверка существования директории шаблонов меню
        if (!is_dir($menuTemplatesPath)) {
            
            // Попытка создания директории при отсутствии
            if (!mkdir($menuTemplatesPath, 0755, true)) {
                // Неудачная попытка создания директории
            }
            return $templates;
        }
        
        // Сканирование директории на наличие PHP файлов шаблонов
        $files = scandir($menuTemplatesPath);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $templateName = pathinfo($file, PATHINFO_FILENAME);
                $templates[$templateName] = $templateName;
            }
        }
        
        return $templates;
    }
    
    /**
     * Валидация структуры меню
     * Проверяет корректность древовидной структуры пунктов меню
     *
     * @param array $structure Структура меню для проверки
     * @return bool Результат валидации
     */
    public function validateMenuStructure($structure) {
        if (!is_array($structure)) {
            return false;
        }
        
        foreach ($structure as $item) {
            // Проверка обязательных полей пункта меню
            if (!isset($item['title']) || empty(trim($item['title']))) {
                return false;
            }
            
            if (!isset($item['url']) || empty(trim($item['url']))) {
                return false;
            }
            
            // Рекурсивная проверка вложенных пунктов меню
            if (isset($item['children']) && is_array($item['children'])) {
                if (!$this->validateMenuStructure($item['children'])) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Получение текущего активного шаблона из настроек
     * Определяет используемую тему из настроек системы
     *
     * @return string Название текущей темы
     */
    public function getCurrentTheme() {
        try {
            $theme = SettingsHelper::get('site', 'site_template');
            
            if (empty($theme)) {
                $theme = SettingsHelper::get('site', 'theme');
            }
            
            if (empty($theme)) {
                $theme = SettingsHelper::getCurrentTemplate();
            }
            
            if (empty($theme)) {
                $theme = 'default';
            }
            
            return $theme;
        } catch (Exception $e) {
            return 'default';
        }
    }

    /**
     * Получение всех активных меню для указанного шаблона
     * Возвращает все активные меню, связанные с конкретным шаблоном
     *
     * @param string $template Название шаблона
     * @return array Массив активных меню для шаблона
     */
    public function getAllByTemplate($template) {
        return $this->db->fetchAll(
            "SELECT * FROM menus WHERE template = ? AND status = 'active' ORDER BY name ASC", 
            [$template]
        );
    }

    /**
     * Получение всех активных меню
     * Возвращает список всех меню со статусом 'active'
     *
     * @return array Массив активных меню
     */
    public function getAllActive() {
        return $this->db->fetchAll(
            "SELECT * FROM menus WHERE status = 'active' ORDER BY name ASC"
        );
    }

    /**
     * Получение меню по ID с проверкой активности
     * Возвращает данные меню только если оно активно
     *
     * @param int $id ID меню
     * @return array|null Данные активного меню или null если не найдено
     */
    public function getActiveById($id) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE id = ? AND status = 'active'", 
            [(int)$id]
        );
    }

    /**
     * Получение меню по названию с проверкой активности
     * Возвращает данные меню только если оно активно
     *
     * @param string $name Название меню
     * @return array|null Данные активного меню или null если не найдено
     */
    public function getActiveByName($name) {
        return $this->db->fetch(
            "SELECT * FROM menus WHERE name = ? AND status = 'active'", 
            [$name]
        );
    }

    /**
     * Получение всех групп пользователей для выбора
     * Возвращает список всех групп пользователей включая группу "Гость"
     *
     * @return array Массив групп пользователей
     */
    public function getAllUserGroups() {
        $groups = $this->db->fetchAll("SELECT * FROM user_groups ORDER BY name");
        
        // Добавление группы "Гость" для неавторизованных пользователей
        $groups[] = [
            'id' => 'guest',
            'name' => 'Гость',
            'description' => 'Неавторизованные пользователи'
        ];
        
        return $groups;
    }

    /**
     * Получение групп пользователя
     * Определяет к каким группам принадлежит указанный пользователь
     *
     * @param int|null $userId ID пользователя (null для неавторизованных)
     * @return array Массив ID групп пользователя
     */
    public function getUserGroups($userId) {
        if (!$userId) {
            return ['guest']; // Неавторизованные пользователи принадлежат к группе "Гость"
        }
        
        $groups = $this->db->fetchAll("
            SELECT ug.id 
            FROM user_groups ug
            JOIN users_groups uug ON ug.id = uug.group_id
            WHERE uug.user_id = ?
        ", [$userId]);
        
        $groupIds = array_column($groups, 'id');
        return $groupIds;
    }

    /**
     * Проверка видимости пункта меню для пользователя
     * Определяет, должен ли пункт меню быть видим для пользователя с указанными группами
     *
     * @param array $item Данные пункта меню
     * @param array $userGroups Группы пользователя
     * @return bool true если пункт меню должен быть видим
     */
    public function shouldShowMenuItem($item, $userGroups) {
        // Если нет настроек видимости - показывать всем
        if (!isset($item['visibility']) || empty($item['visibility'])) {
            return true;
        }
        
        $visibility = $item['visibility'];
        
        // Проверка настроек "показывать группам"
        if (!empty($visibility['show_to_groups'])) {
            $hasMatchingGroup = false;
            foreach ($visibility['show_to_groups'] as $groupId) {
                if (in_array($groupId, $userGroups)) {
                    $hasMatchingGroup = true;
                    break;
                }
            }
            if (!$hasMatchingGroup) {
                return false;
            }
        }
        
        // Проверка настроек "не показывать группам"
        if (!empty($visibility['hide_from_groups'])) {
            foreach ($visibility['hide_from_groups'] as $groupId) {
                if (in_array($groupId, $userGroups)) {
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Фильтрация структуры меню по группам пользователя
     * Удаляет из структуры меню пункты, недоступные для указанных групп пользователя
     *
     * @param array $structure Исходная структура меню
     * @param array $userGroups Группы пользователя
     * @return array Отфильтрованная структура меню
     */
    public function filterMenuByUserGroups($structure, $userGroups) {
        $filteredStructure = [];
        
        foreach ($structure as $item) {
            // Проверка видимости текущего пункта
            if ($this->shouldShowMenuItem($item, $userGroups)) {
                $filteredItem = $item;
                
                // Рекурсивная фильтрация вложенных пунктов
                if (!empty($item['children'])) {
                    $filteredItem['children'] = $this->filterMenuByUserGroups($item['children'], $userGroups);
                }
                
                $filteredStructure[] = $filteredItem;
            }
        }
        
        return $filteredStructure;
    }

}