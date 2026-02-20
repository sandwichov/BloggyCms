<?php

namespace menu\actions;

/**
 * Действие создания нового меню в админ-панели
 * Обрабатывает форму создания меню с валидацией структуры и настроек видимости
 * 
 * @package menu\actions
 * @extends MenuAction
 */
class AdminCreate extends MenuAction {
    
    /**
     * Метод выполнения создания меню
     * Обрабатывает форму создания, валидирует данные и сохраняет новое меню
     * 
     * @return void
     */
    public function execute() {
        // Получение доступных шаблонов меню из текущей темы
        $availableTemplates = $this->menuModel->getAvailableTemplates();
        $currentTheme = $this->menuModel->getCurrentTheme();
        
        // Обработка POST-запроса (отправка формы создания)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Валидация обязательных полей формы
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
                
                // Декодирование и валидация структуры меню
                $menuStructure = json_decode($_POST['menu_structure'] ?? '[]', true);
                if (!$this->menuModel->validateMenuStructure($menuStructure)) {
                    throw new \Exception('Некорректная структура меню');
                }
                
                // ВАЛИДАЦИЯ И ОБРАБОТКА НАСТРОЕК ВИДИМОСТИ
                $this->validateAndProcessVisibilitySettings($menuStructure);
                
                // Подготовка данных для сохранения
                $menuData = [
                    'name' => trim($_POST['name']),
                    'template' => $_POST['template'],
                    'structure' => json_encode($menuStructure, JSON_UNESCAPED_UNICODE),
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Создание меню в базе данных
                $menuId = $this->menuModel->create($menuData);
                
                // Уведомление об успешном создании
                \Notification::success('Меню успешно создано');
                
                // Перенаправление на страницу управления меню
                $this->redirect(ADMIN_URL . '/menu');
                
            } catch (\Exception $e) {
                // Обработка ошибок создания
                \Notification::error($e->getMessage());
                
                // Повторный рендеринг формы с сохраненными данными
                $this->render('admin/menu/form', [
                    'menu' => $_POST,
                    'availableTemplates' => $availableTemplates,
                    'menuStructure' => $menuStructure ?? [],
                    'currentTheme' => $currentTheme,
                    'pageTitle' => 'Создание меню'
                ]);
                return;
            }
        }
        
        // Рендеринг пустой формы для GET-запроса
        $this->render('admin/menu/form', [
            'menu' => [],
            'availableTemplates' => $availableTemplates,
            'menuStructure' => [],
            'currentTheme' => $currentTheme,
            'pageTitle' => 'Создание меню'
        ]);
    }
    
    /**
     * Валидация и обработка настроек видимости для структуры меню
     * Проверяет и фильтрует настройки групп пользователей для пунктов меню
     *
     * @param array &$menuStructure Ссылка на структуру меню для обработки
     * @return void
     */
    private function validateAndProcessVisibilitySettings(&$menuStructure) {
        if (!is_array($menuStructure)) {
            return;
        }
        
        // Обработка каждого пункта меню в структуре
        foreach ($menuStructure as &$item) {
            $this->processMenuItemVisibility($item);
            
            // Рекурсивная обработка вложенных элементов
            if (!empty($item['children']) && is_array($item['children'])) {
                $this->validateAndProcessVisibilitySettings($item['children']);
            }
        }
    }
    
    /**
     * Обработка настроек видимости для одного пункта меню
     * Валидирует и фильтрует группы пользователей для отображения/скрытия пункта
     *
     * @param array &$item Ссылка на пункт меню для обработки
     * @return void
     */
    private function processMenuItemVisibility(&$item) {
        if (!isset($item['visibility'])) {
            return;
        }
        
        $visibility = $item['visibility'];
        
        // Валидация групп для показа
        if (isset($visibility['show_to_groups']) && is_array($visibility['show_to_groups'])) {
            $validGroups = $this->getValidUserGroups();
            $filteredShowGroups = [];
            
            // Фильтрация валидных групп для показа
            foreach ($visibility['show_to_groups'] as $groupId) {
                if ($this->isValidGroupId($groupId, $validGroups)) {
                    $filteredShowGroups[] = $groupId;
                }
            }
            
            $item['visibility']['show_to_groups'] = $filteredShowGroups;
        } else {
            $item['visibility']['show_to_groups'] = [];
        }
        
        // Валидация групп для скрытия
        if (isset($visibility['hide_from_groups']) && is_array($visibility['hide_from_groups'])) {
            $validGroups = $this->getValidUserGroups();
            $filteredHideGroups = [];
            
            // Фильтрация валидных групп для скрытия
            foreach ($visibility['hide_from_groups'] as $groupId) {
                if ($this->isValidGroupId($groupId, $validGroups)) {
                    $filteredHideGroups[] = $groupId;
                }
            }
            
            $item['visibility']['hide_from_groups'] = $filteredHideGroups;
        } else {
            $item['visibility']['hide_from_groups'] = [];
        }
        
        // Удаление объекта видимости если обе настройки пустые
        if (empty($item['visibility']['show_to_groups']) && empty($item['visibility']['hide_from_groups'])) {
            unset($item['visibility']);
        }
    }
    
    /**
     * Получение списка валидных групп пользователей
     * Возвращает массив ID всех существующих групп пользователей включая группу "Гость"
     *
     * @return array Массив валидных ID групп пользователей
     */
    private function getValidUserGroups() {
        $userModel = new \UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        // Добавление группы "Гость" для неавторизованных пользователей
        $groups[] = ['id' => 'guest', 'name' => 'Гость'];
        
        $validGroups = ['guest']; // Всегда включаем гостя
        foreach ($groups as $group) {
            $validGroups[] = $group['id'];
        }
        
        return $validGroups;
    }
    
    /**
     * Проверка валидности ID группы
     * Определяет, существует ли указанный ID группы в системе
     *
     * @param string|int $groupId Проверяемый ID группы
     * @param array $validGroups Массив валидных ID групп
     * @return bool true если группа существует
     */
    private function isValidGroupId($groupId, $validGroups) {
        return in_array($groupId, $validGroups);
    }
}