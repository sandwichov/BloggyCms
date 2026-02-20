<?php

namespace fields\actions;

/**
 * Действие отображения общего списка полей в админ-панели
 * Показывает все пользовательские поля системы с группировкой по типам сущностей
 * Предоставляет общий обзор всех дополнительных полей в CMS
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminIndex extends FieldAction {
    
    /**
     * Метод выполнения отображения списка полей
     * Загружает все поля системы и отображает их с информацией об использовании
     * 
     * @return void
     */
    public function execute() {
        // Получение всех полей системы с количеством их использования
        $fields = $this->fieldModel->getAll();
        
        /**
         * Рендеринг главной страницы управления полями
         * 
         * @param string $template Путь к шаблону (admin/fields/index)
         * @param array $data Данные для шаблона:
         * - fields: массив всех полей системы с данными об использовании
         * - fieldModel: модель полей для дополнительных операций
         * - pageTitle: заголовок страницы
         */
        $this->render('admin/fields/index', [
            'fields' => $fields,
            'fieldModel' => $this->fieldModel,
            'pageTitle' => 'Управление полями'
        ]);
    }
}