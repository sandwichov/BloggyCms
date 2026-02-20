<?php

namespace menu\actions;

/**
 * Действие редактирования меню в админ-панели
 * Отображает форму редактирования существующего меню и обрабатывает её отправку
 * 
 * @package menu\actions
 * @extends MenuAction
 */
class AdminEdit extends MenuAction {
    
    /**
     * Метод выполнения редактирования меню
     * Отображает форму с текущими данными меню и обрабатывает POST-запросы для обновления
     * 
     * @return void
     */
    public function execute() {
        // Получение ID меню из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID меню
        if (!$id) {
            \Notification::error('ID меню не указан');
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        // Получение данных меню по ID
        $menu = $this->menuModel->getById($id);
        
        // Проверка существования меню
        if (!$menu) {
            \Notification::error('Меню не найдено');
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        // Получение дополнительных данных для формы
        $availableTemplates = $this->menuModel->getAvailableTemplates();
        $currentTheme = $this->menuModel->getCurrentTheme();
        $menuStructure = json_decode($menu['structure'], true) ?: [];
        
        // Обработка POST-запроса (отправка формы)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $this->handlePostRequest($id, $menu, $menuStructure, $availableTemplates);
            } catch (\Exception $e) {
                \Notification::error($e->getMessage());
                
                // Восстановление данных из POST при ошибке
                $menu = array_merge($menu, $_POST);
                $menuStructure = $menuStructure ?? json_decode($menu['structure'], true) ?: [];
            }
        }
        
        // Отображение формы редактирования
        $this->render('admin/menu/form', [
            'menu' => $menu,
            'availableTemplates' => $availableTemplates,
            'menuStructure' => $menuStructure,
            'currentTheme' => $currentTheme,
            'pageTitle' => 'Редактирование меню: ' . htmlspecialchars($menu['name'])
        ]);
    }
    
    /**
     * Обрабатывает POST-запрос на обновление меню
     * Выполняет валидацию и сохраняет изменения в базе данных
     * 
     * @param int|string $id ID редактируемого меню
     * @param array $menu Текущие данные меню
     * @param array $menuStructure Текущая структура меню
     * @param array $availableTemplates Доступные шаблоны меню
     * @throws \Exception При ошибках валидации или сохранения
     * @return void
     */
    private function handlePostRequest($id, &$menu, &$menuStructure, $availableTemplates) {
        // Валидация обязательных полей
        if (empty($_POST['name'])) {
            throw new \Exception('Название меню обязательно');
        }
        
        if (empty($_POST['template'])) {
            throw new \Exception('Шаблон меню обязателен');
        }
        
        // Проверка существования выбранного шаблона
        if (!isset($availableTemplates[$_POST['template']])) {
            throw new \Exception('Указанный шаблон не существует в текущей теме');
        }
        
        // Валидация структуры меню
        $menuStructure = json_decode($_POST['menu_structure'] ?? '[]', true);
        if (!$this->menuModel->validateMenuStructure($menuStructure)) {
            throw new \Exception('Некорректная структура меню');
        }
        
        // Обработка настроек видимости пунктов меню
        $this->validateAndProcessVisibilitySettings($menuStructure);
        
        // Подготовка данных для обновления
        $menuData = [
            'name' => trim($_POST['name']),
            'template' => $_POST['template'],
            'structure' => json_encode($menuStructure, JSON_UNESCAPED_UNICODE),
            'status' => $_POST['status'] ?? 'active',
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Выполнение обновления
        $success = $this->menuModel->update($id, $menuData);
        
        if ($success) {
            \Notification::success('Меню успешно обновлено');
            $this->redirect(ADMIN_URL . '/menu');
        } else {
            throw new \Exception('Не удалось обновить меню');
        }
    }
    
    /**
     * Валидирует и обрабатывает настройки видимости для структуры меню
     * Рекурсивно обходит все пункты меню и их дочерние элементы
     * 
     * @param array &$menuStructure Структура меню для обработки (передается по ссылке)
     * @return void
     */
    private function validateAndProcessVisibilitySettings(&$menuStructure) {
        if (!is_array($menuStructure)) {
            return;
        }
        
        foreach ($menuStructure as &$item) {
            $this->processMenuItemVisibility($item);
            
            // Рекурсивная обработка дочерних элементов
            if (!empty($item['children']) && is_array($item['children'])) {
                $this->validateAndProcessVisibilitySettings($item['children']);
            }
        }
    }
    
    /**
     * Обрабатывает настройки видимости для одного пункта меню
     * Фильтрует группы пользователей и удаляет конфликтующие настройки
     * 
     * @param array &$item Пункт меню для обработки (передается по ссылке)
     * @return void
     */
    private function processMenuItemVisibility(&$item) {
        if (!isset($item['visibility'])) {
            return;
        }
        
        $visibility = $item['visibility'];
        $validGroups = $this->getValidUserGroups();
        
        // Обработка групп для показа
        $item['visibility']['show_to_groups'] = $this->filterValidGroups(
            $visibility['show_to_groups'] ?? [], 
            $validGroups
        );
        
        // Обработка групп для скрытия
        $item['visibility']['hide_from_groups'] = $this->filterValidGroups(
            $visibility['hide_from_groups'] ?? [], 
            $validGroups
        );
        
        // Удаление пустых настроек видимости
        if (empty($item['visibility']['show_to_groups']) && empty($item['visibility']['hide_from_groups'])) {
            unset($item['visibility']);
            return;
        }
        
        // Проверка конфликтов настроек
        $this->checkVisibilityConflicts($item['visibility']);
    }
    
    /**
     * Фильтрует массив ID групп, оставляя только валидные
     * 
     * @param array $groupIds Массив ID групп для фильтрации
     * @param array $validGroups Массив валидных ID групп
     * @return array Отфильтрованный массив валидных ID групп
     */
    private function filterValidGroups($groupIds, $validGroups) {
        if (!is_array($groupIds)) {
            return [];
        }
        
        $filteredGroups = [];
        foreach ($groupIds as $groupId) {
            if ($this->isValidGroupId($groupId, $validGroups)) {
                $filteredGroups[] = $groupId;
            }
        }
        
        return $filteredGroups;
    }
    
    /**
     * Проверяет конфликтующие настройки видимости
     * Удаляет группы, которые одновременно указаны для показа и скрытия
     * 
     * @param array &$visibility Настройки видимости для проверки (передается по ссылке)
     * @return void
     */
    private function checkVisibilityConflicts(&$visibility) {
        $showGroups = $visibility['show_to_groups'] ?? [];
        $hideGroups = $visibility['hide_from_groups'] ?? [];
        
        $conflictingGroups = array_intersect($showGroups, $hideGroups);
        
        if (!empty($conflictingGroups)) {
            $visibility['hide_from_groups'] = array_diff($hideGroups, $conflictingGroups);
        }
    }
    
    /**
     * Получает список валидных групп пользователей
     * Включает системную группу "guest" и все группы из базы данных
     * 
     * @return array Массив валидных ID групп
     */
    private function getValidUserGroups() {
        $userModel = new \UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        $validGroups = ['guest'];
        foreach ($groups as $group) {
            $validGroups[] = $group['id'];
        }
        
        return $validGroups;
    }
    
    /**
     * Проверяет валидность ID группы
     * 
     * @param mixed $groupId ID группы для проверки
     * @param array $validGroups Массив валидных ID групп
     * @return bool true если группа валидна, false в противном случае
     */
    private function isValidGroupId($groupId, $validGroups) {
        return in_array($groupId, $validGroups);
    }
}