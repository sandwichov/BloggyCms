<?php

/**
 * Контроллер управления дополнительными полями в админ-панели
 * Предоставляет административный интерфейс для создания, редактирования и управления
 * пользовательскими полями для различных сущностей системы (посты, категории, пользователи)
 * 
 * @package controllers
 * @extends Controller
 */
class AdminFieldsController extends Controller {
    
    /**
     * @var FieldModel Модель для работы с дополнительными полями
     */
    private $fieldModel;
    
    /**
     * Конструктор контроллера управления полями
     * Инициализирует модель полей и проверяет права администратора
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        
        // Инициализация модели полей
        $this->fieldModel = new FieldModel($db);
        
        // Проверка административных прав доступа
        if (!$this->checkAdminAccess()) {
            \Notification::error('Доступ запрещен');
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
    }
    
    /**
     * Проверка прав администратора
     * Проверяет наличие административных прав в сессии пользователя
     *
     * @return bool true если пользователь имеет административные права
     */
    private function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Действие: Главная страница управления полями
     * Отображает общий обзор всех дополнительных полей в системе
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $action = new \fields\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Управление полями для конкретной сущности
     * Показывает все поля, связанные с определенным типом сущности
     * 
     * @param string $entityType Тип сущности (post, category, user и т.д.)
     * @return mixed
     */
    public function entityAction($entityType) {
        $action = new \fields\actions\AdminEntity($this->db, ['entityType' => $entityType]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Создание нового поля
     * Отображает форму создания нового дополнительного поля для указанной сущности
     * 
     * @param string $entityType Тип сущности, для которой создается поле
     * @return mixed
     */
    public function createAction($entityType) {
        $action = new \fields\actions\AdminCreate($this->db, ['entityType' => $entityType]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Редактирование существующего поля
     * Отображает форму редактирования настроек дополнительного поля
     * 
     * @param int $id ID редактируемого поля
     * @return mixed
     */
    public function editAction($id) {
        $action = new \fields\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Действие: Удаление поля
     * Выполняет удаление дополнительного поля по его ID
     * 
     * @param int $id ID удаляемого поля
     * @return mixed
     */
    public function deleteAction($id) {
        $action = new \fields\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Действие: Переключение состояния поля (включение/выключение)
     * Изменяет активность поля без его удаления из системы
     * 
     * @param int $id ID поля, состояние которого изменяется
     * @return mixed
     */
    public function toggleAction($id) {
        $action = new \fields\actions\AdminToggle($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Действие: Получение настроек типа поля
     * Возвращает JSON-данные с настройками для определенного типа поля
     * Используется для динамического обновления формы создания/редактирования
     * 
     * @param string $type Тип поля (text, textarea, select, checkbox и т.д.)
     * @return mixed JSON-ответ с настройками
     */
    public function getSettingsAction($type) {
        $action = new \fields\actions\AdminGetSettings($this->db, ['type' => $type]);
        $action->setController($this);
        return $action->execute();
    }
}