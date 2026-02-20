<?php

namespace fields\actions;

/**
 * Действие получения настроек типа поля через AJAX
 * Динамически загружает форму настроек для указанного типа поля
 * Используется для обновления интерфейса при смене типа поля в формах создания/редактирования
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminGetSettings extends FieldAction {
    
    /**
     * Метод выполнения получения настроек типа поля
     * Возвращает HTML-форму настроек для указанного типа поля через FieldManager
     * 
     * @return void
     */
    public function execute() {
        // Получение типа поля из параметров
        $type = $this->params['type'] ?? null;
        
        // Проверка наличия типа поля
        if (!$type) {
            echo '<div class="alert alert-warning">Тип поля не указан</div>';
            exit;
        }
        
        // Получение текущей конфигурации из POST-данных
        $config = $_POST['config'] ?? [];
        
        // Создание экземпляра FieldManager
        $fieldManager = new \FieldManager($this->db);
        
        // Получение экземпляра поля для указанного типа
        $fieldInstance = $fieldManager->getFieldInstance($type, $config);
        
        // Проверка успешности создания экземпляра поля
        if ($fieldInstance) {
            // Вывод HTML-формы настроек поля
            echo $fieldInstance->getSettingsForm();
        } else {
            // Сообщение об ошибке если тип поля не поддерживается
            echo '<div class="alert alert-warning">Настройки для этого типа поля не найдены</div>';
        }
        
        // Завершение выполнения скрипта после вывода настроек
        exit;
    }
}