<?php

/**
 * Контроллер управления меню в админ-панели
 * Предоставляет интерфейс для создания, редактирования и управления меню сайта
 * 
 * @package controllers
 * @extends Controller
 */
class AdminMenuController extends Controller {
    
    /**
     * @var MenuModel Модель для работы с меню
     */
    private $menuModel;
    
    /**
     * Конструктор контроллера меню
     * Инициализирует модель меню и проверяет права администратора
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->menuModel = new MenuModel($db);
        
        // Проверка авторизации пользователя
        if (!isset($_SESSION['user_id'])) {
            \Notification::error('Пожалуйста, авторизуйтесь для доступа к панели управления');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        // Проверка административных прав
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            \Notification::error('У вас нет прав доступа к панели управления');
            $this->redirect(BASE_URL);
            return;
        }
    }
    
    /**
     * Действие: Главная страница управления меню
     * Отображает список всех меню в системе
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $action = new \menu\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Создание нового меню
     * Отображает форму создания меню
     * 
     * @return mixed
     */
    public function createAction() {
        $action = new \menu\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Редактирование существующего меню
     * Отображает форму редактирования меню по его ID
     * 
     * @param int $id ID редактируемого меню
     * @return mixed
     */
    public function editAction($id) {
        $action = new \menu\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Удаление меню
     * Удаляет меню по его ID
     * 
     * @param int $id ID удаляемого меню
     * @return mixed
     */
    public function deleteAction($id) {
        $action = new \menu\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Получение структуры меню через AJAX
     * Возвращает JSON с древовидной структурой пунктов меню
     * 
     * @param int $id ID меню
     * @return mixed JSON-ответ со структурой меню
     */
    public function getStructureAction($id) {
        $action = new \menu\actions\AdminGetStructure($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Предварительный просмотр меню
     * Показывает как будет выглядеть меню на сайте
     * 
     * @param int $id ID меню для предпросмотра
     * @return mixed
     */
    public function previewAction($id) {
        $action = new \menu\actions\AdminPreview($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Рендеринг одного пункта меню для формы (рекурсивно)
     * Генерирует HTML-структуру для редактирования пункта меню с поддержкой вложенности
     *
     * @param array $item Данные пункта меню
     * @param string $index Уникальный индекс пункта в структуре
     * @return string HTML-код пункта меню
     */
    public function renderMenuItem($item, $index) {
        $title = htmlspecialchars($item['title'] ?? '');
        $url = htmlspecialchars($item['url'] ?? '');
        $class = htmlspecialchars($item['class'] ?? '');
        $target = $item['target'] ?? '_self';
        $children = $item['children'] ?? [];
        
        ob_start();
        ?>
        <div class="menu-item card mb-2" data-index="<?= $index ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">Пункт меню</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary menu-item-handle">
                            <i class="bi bi-arrows-move"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary toggle-children">
                            <i class="bi bi-list-nested"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger remove-menu-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small">Название</label>
                            <input type="text" 
                                class="form-control form-control-sm menu-item-title" 
                                placeholder="Название пункта" 
                                maxlength="100"
                                value="<?= $title ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small">URL</label>
                            <input type="text" 
                                class="form-control form-control-sm menu-item-url" 
                                placeholder="/page" 
                                maxlength="255"
                                value="<?= $url ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small">CSS класс</label>
                            <input type="text" 
                                class="form-control form-control-sm menu-item-class" 
                                placeholder="css-class" 
                                maxlength="50"
                                value="<?= $class ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small">Открывать в</label>
                            <select class="form-select form-select-sm menu-item-target">
                                <option value="_self" <?= $target === '_self' ? 'selected' : '' ?>>Текущее окно</option>
                                <option value="_blank" <?= $target === '_blank' ? 'selected' : '' ?>>Новое окно</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="menu-children-container mt-3" style="display: <?= !empty($children) ? 'block' : 'none' ?>;">
                    <div class="border-top pt-3">
                        <h6 class="small text-muted mb-2">Вложенные пункты</h6>
                        <div class="menu-children sortable-menu">
                            <?php if (!empty($children)): ?>
                                <?php foreach ($children as $childIndex => $child): ?>
                                    <?= $this->renderMenuItem($child, $index . '_' . $childIndex) ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm add-child-item">
                            <i class="bi bi-plus-circle me-1"></i>Добавить вложенный пункт
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Получение всех групп пользователей
     * Возвращает список групп пользователей включая группу "Гость"
     *
     * @return array Массив групп пользователей
     */
    public function getUserGroups() {
        $userModel = new UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        // Добавление группы "Гость" для неавторизованных пользователей
        $groups[] = [
            'id' => 'guest',
            'name' => 'Гость',
            'description' => 'Неавторизованные пользователи'
        ];
        
        return $groups;
    }

}