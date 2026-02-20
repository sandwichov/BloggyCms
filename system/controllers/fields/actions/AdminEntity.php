<?php

namespace fields\actions;

/**
 * Действие отображения полей для конкретной сущности в админ-панели
 * Показывает список всех пользовательских полей, связанных с определенным типом сущности
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminEntity extends FieldAction {
    
    /**
     * Метод выполнения отображения полей сущности
     * Загружает и отображает все поля, связанные с указанным типом сущности
     * 
     * @return void
     */
    public function execute() {
        // Получение типа сущности из параметров
        $entityType = $this->params['entityType'] ?? null;
        
        // Проверка наличия типа сущности
        if (!$entityType) {
            \Notification::error('Тип сущности не указан');
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        // Получение всех полей для указанного типа сущности
        $fields = $this->fieldModel->getByEntityType($entityType);
        
        /**
         * Рендеринг страницы управления полями сущности
         * 
         * @param string $template Путь к шаблону (admin/fields/entity)
         * @param array $data Данные для шаблона:
         * - fields: массив полей для данной сущности
         * - entityType: тип сущности (post, category, user и т.д.)
         * - entityName: читаемое имя сущности (Посты, Категории, Пользователи)
         * - fieldModel: модель полей для дополнительных операций
         * - pageTitle: заголовок страницы с названием сущности
         */
        $this->render('admin/fields/entity', [
            'fields' => $fields,
            'entityType' => $entityType,
            'entityName' => $this->getEntityName($entityType),
            'fieldModel' => $this->fieldModel,
            'pageTitle' => 'Поля для ' . $this->getEntityName($entityType)
        ]);
    }
}