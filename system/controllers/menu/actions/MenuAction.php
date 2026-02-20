<?php

namespace menu\actions;

/**
 * Абстрактный базовый класс для всех действий модуля управления меню
 * Предоставляет общую функциональность и вспомогательные методы для действий
 * 
 * @package menu\actions
 */
abstract class MenuAction {
    
    /** @var object Подключение к базе данных */
    protected $db;
    
    /** @var array Параметры запроса */
    protected $params;
    
    /** @var object Контроллер, вызывающий действие */
    protected $controller;
    
    /** @var \MenuModel Модель для работы с меню */
    protected $menuModel;
    
    /**
     * Конструктор класса действия
     * 
     * @param object $db Подключение к базе данных
     * @param array $params Параметры запроса (по умолчанию [])
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->menuModel = new \MenuModel($db);
    }
    
    /**
     * Устанавливает контроллер, вызывающий действие
     * 
     * @param object $controller Контроллер
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в классах-наследниках
     * 
     * @return void
     */
    abstract public function execute();
    
    /**
     * Рендерит шаблон с переданными данными
     * Использует контроллер для рендеринга, если он установлен
     * 
     * @param string $template Путь к шаблону
     * @param array $data Данные для передачи в шаблон
     * @throws \Exception Если контроллер не установлен
     * @return void
     */
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
     * Выполняет перенаправление на указанный URL
     * Использует контроллер для перенаправления, если он установлен
     * 
     * @param string $url URL для перенаправления
     * @return void
     */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
     * Рендерит отдельный пункт меню для административного интерфейса
     * Использует метод контроллера, если он существует
     * 
     * @param array $item Данные пункта меню
     * @param int $index Индекс пункта в структуре
     * @return string HTML-код пункта меню
     */
    protected function renderMenuItem($item, $index) {
        if ($this->controller && method_exists($this->controller, 'renderMenuItem')) {
            return $this->controller->renderMenuItem($item, $index);
        }
        return '';
    }
    
    /**
     * Рендерит HTML-код для предпросмотра меню
     * Создает структуру UL/LI на основе переданной структуры меню
     * 
     * @param array $structure Структура меню
     * @return string HTML-код меню для предпросмотра
     */
    protected function renderMenuPreview($structure) {
        $html = '<ul class="nav">';
        
        foreach ($structure as $item) {
            $html .= $this->renderPreviewMenuItem($item);
        }
        
        $html .= '</ul>';
        return $html;
    }

    /**
     * Рекурсивно рендерит отдельный пункт меню для предпросмотра
     * Обрабатывает дочерние элементы и применяет атрибуты
     * 
     * @param array $item Данные пункта меню
     * @param int $level Уровень вложенности (по умолчанию 0)
     * @return string HTML-код пункта меню
     */
    protected function renderPreviewMenuItem($item, $level = 0) {
        // Экранирование и подготовка атрибутов
        $class = $item['class'] ?? '';
        $target = $item['target'] ?? '_self';
        $title = htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($item['url'] ?? '#', ENT_QUOTES, 'UTF-8');
        
        // Формирование HTML
        $html = '<li class="nav-item' . ($class ? ' ' . $class : '') . '">';
        $html .= '<a href="' . $url . '" target="' . $target . '" class="nav-link">' . $title . '</a>';
        
        // Рекурсивная обработка дочерних элементов
        if (!empty($item['children'])) {
            $html .= '<ul class="nav flex-column ms-3">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderPreviewMenuItem($child, $level + 1);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }

    /**
     * Рендерит HTML-код настроек видимости для пункта меню
     * Отображает два селекта для выбора групп показа и скрытия
     * 
     * @param array $item Данные пункта меню (по умолчанию [])
     * @return string HTML-код настроек видимости
     */
    protected function renderVisibilitySettings($item = []) {
        $groups = $this->getUserGroups();
        $currentShowTo = $item['visibility']['show_to_groups'] ?? [];
        $currentHideFrom = $item['visibility']['hide_from_groups'] ?? [];
        
        ob_start();
        ?>
        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label small">
                    <i class="bi bi-eye me-1"></i>Показывать группам
                </label>
                <select class="form-select form-select-sm menu-item-show-to" multiple size="4">
                    <option value="">Все группы (если не выбрано)</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']) ?>" 
                            <?= in_array($group['id'], $currentShowTo) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small">Оставьте пустым чтобы показывать всем</div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label small">
                    <i class="bi bi-eye-slash me-1"></i>Не показывать группам
                </label>
                <select class="form-select form-select-sm menu-item-hide-from" multiple size="4">
                    <option value="">Никому не скрывать</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?= htmlspecialchars($group['id']) ?>" 
                            <?= in_array($group['id'], $currentHideFrom) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($group['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text small">Выберите группы которым скрыть этот пункт</div>
            </div>
        </div>
        
        <div class="alert alert-info mt-2 p-2 small">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Приоритет:</strong> Сначала проверяется "Показывать группам", затем "Не показывать группам"
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Получает список всех групп пользователей
     * Включает системную группу "Гость" и все группы из базы данных
     * 
     * @return array Массив групп с полями id, name, description
     */
    protected function getUserGroups() {
        $userModel = new \UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        // Добавление системной группы "Гость"
        $groups[] = [
            'id' => 'guest',
            'name' => 'Гость',
            'description' => 'Неавторизованные пользователи'
        ];
        
        return $groups;
    }
}