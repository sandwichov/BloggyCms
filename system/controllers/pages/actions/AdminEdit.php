<?php

namespace pages\actions;

/**
 * Действие редактирования страницы в административной панели
 * Отображает форму редактирования существующей страницы с её блоками и полями,
 * обрабатывает сохранение изменений, поддерживает как обычные, так и AJAX-запросы
 * 
 * @package pages\actions
 * @extends PageAction
 */
class AdminEdit extends PageAction {
    
    /** @var int|null ID редактируемой страницы */
    protected $id;
    
    /**
     * Устанавливает ID страницы для редактирования
     * 
     * @param int|null $id ID страницы
     * @return void
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Метод выполнения редактирования страницы
     * Проверяет права доступа, загружает данные страницы, её блоки и поля,
     * отображает форму или обрабатывает сохранение
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            $this->handleAccessDenied();
            return;
        }
        
        // Проверка наличия ID страницы
        if (!$this->validatePageId()) {
            return;
        }
        
        try {
            // Загрузка данных страницы
            $page = $this->loadPage();
            
            // Загрузка всех ассетов блоков для редактора
            $this->postBlockManager->loadAllPostBlockAssets();
            
            // Загрузка и подготовка блоков страницы
            $preparedBlocks = $this->loadAndPrepareBlocks();
            
            // Обработка POST-запроса (сохранение изменений)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($page);
                return;
            }
            
            // Отображение формы редактирования
            $this->renderEditForm($page, $preparedBlocks);
            
        } catch (\Exception $e) {
            $this->handleLoadError($e);
        }
    }
    
    /**
     * Проверяет наличие ID страницы для редактирования
     * 
     * @return bool true если ID указан, false в противном случае
     */
    private function validatePageId() {
        if (!$this->id) {
            \Notification::error('ID страницы не указан');
            $this->redirect(ADMIN_URL . '/pages');
            return false;
        }
        return true;
    }
    
    /**
     * Загружает данные страницы из базы данных
     * 
     * @return array Данные страницы
     * @throws \Exception Если страница не найдена
     */
    private function loadPage() {
        $page = $this->pageModel->getById($this->id);
        
        if (!$page) {
            throw new \Exception('Страница не найдена');
        }
        
        return $page;
    }
    
    /**
     * Загружает и подготавливает блоки страницы для отображения в форме
     * Декодирует JSON-данные, нормализует структуру контента и настроек
     * 
     * @return array Массив подготовленных блоков
     */
    private function loadAndPrepareBlocks() {
        $blocks = $this->postBlockModel->getByPage($this->id);
        $preparedBlocks = [];
        
        foreach ($blocks as $block) {
            $preparedBlocks[] = $this->prepareBlockData($block);
        }
        
        return $preparedBlocks;
    }
    
    /**
     * Подготавливает данные одного блока для отображения в форме
     * 
     * @param array $block Данные блока из БД
     * @return array Подготовленные данные блока
     */
    private function prepareBlockData($block) {
        // Обработка контента блока
        $content = $this->normalizeBlockContent($block['content'], $block['type']);
        
        // Обработка настроек блока
        $settings = $this->normalizeBlockSettings($block['settings']);
        
        return [
            'id' => 'block_' . $block['id'],
            'type' => $block['type'],
            'content' => $content,
            'settings' => $settings,
            'order' => (int)($block['order'] ?? 0)
        ];
    }
    
    /**
     * Нормализует контент блока для использования в форме
     * 
     * @param mixed $content Контент блока
     * @param string $blockType Тип блока
     * @return array Нормализованный контент
     */
    private function normalizeBlockContent($content, $blockType) {
        // Декодирование JSON, если необходимо
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $content = $decoded;
            } else {
                $content = ['text' => $content];
            }
        }
        
        // Приведение к массиву
        if (!is_array($content)) {
            $content = ['text' => (string)$content];
        }
        
        // Специфичная обработка для ListBlock
        if ($blockType === 'ListBlock' && isset($content['items']) && !is_array($content['items'])) {
            $content['items'] = [['text' => (string)$content['items']]];
        }
        
        return $content;
    }
    
    /**
     * Нормализует настройки блока для использования в форме
     * 
     * @param mixed $settings Настройки блока
     * @return array Нормализованные настройки
     */
    private function normalizeBlockSettings($settings) {
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }
        
        return is_array($settings) ? $settings : [];
    }
    
    /**
     * Обрабатывает POST-запрос на сохранение изменений страницы
     * 
     * @param array $page Данные страницы
     * @return void
     */
    private function handlePostRequest($page) {
        try {
            // Валидация обязательных полей
            $this->validateRequiredFields();
            
            // Подготовка и сохранение основных данных страницы
            $this->updatePageData();
            
            // Сохранение блоков страницы
            $this->updatePageBlocks();
            
            // Сохранение пользовательских полей
            $this->updateCustomFields();
            
            // Обработка успешного сохранения
            $this->handleUpdateSuccess();
            
        } catch (\Exception $e) {
            $this->handleUpdateError($e);
        }
    }
    
    /**
     * Проверяет обязательные поля формы
     * 
     * @throws \Exception Если обязательные поля не заполнены
     */
    private function validateRequiredFields() {
        if (empty($_POST['title'])) {
            throw new \Exception('Заголовок страницы обязателен для заполнения');
        }
    }
    
    /**
     * Обновляет основные данные страницы
     */
    private function updatePageData() {
        $data = [
            'title' => $_POST['title'],
            'status' => $_POST['status'] ?? 'draft'
        ];
        
        $this->pageModel->update($this->id, $data);
    }
    
    /**
     * Обновляет блоки страницы
     * 
     * @throws \Exception При неверном формате данных блоков
     */
    private function updatePageBlocks() {
        if (!empty($_POST['post_blocks'])) {
            $blocksData = json_decode($_POST['post_blocks'], true);
            
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($blocksData)) {
                throw new \Exception('Неверный формат данных блоков');
            }
            
            // Обновление блоков (удаление старых, создание новых)
            $this->processPageBlocks($this->id, $blocksData);
        } else {
            // Если блоков нет, удаляем все существующие
            $this->postBlockModel->deleteByPage($this->id);
        }
    }
    
    /**
     * Обновляет пользовательские поля страницы
     */
    private function updateCustomFields() {
        $fieldModel = new \FieldModel($this->db);
        $fieldManager = new \FieldManager($this->db);
        
        // Получение активных полей для страниц
        $customFields = $fieldModel->getActiveByEntityType('page');
        
        // Получение текущих значений полей
        $currentValues = $this->getCurrentFieldValues($fieldModel, $customFields);
        
        // Обработка каждого поля
        foreach ($customFields as $field) {
            $this->processCustomField($field, $fieldModel, $fieldManager, $currentValues);
        }
    }
    
    /**
     * Получает текущие значения полей страницы
     * 
     * @param \FieldModel $fieldModel Модель полей
     * @param array $customFields Массив полей
     * @return array Массив текущих значений
     */
    private function getCurrentFieldValues($fieldModel, $customFields) {
        $currentValues = [];
        foreach ($customFields as $field) {
            $currentValues[$field['system_name']] = $fieldModel->getFieldValue('page', $this->id, $field['system_name']);
        }
        return $currentValues;
    }
    
    /**
     * Обрабатывает одно пользовательское поле
     * 
     * @param array $field Данные поля
     * @param \FieldModel $fieldModel Модель полей
     * @param \FieldManager $fieldManager Менеджер полей
     * @param array $currentValues Текущие значения
     */
    private function processCustomField($field, $fieldModel, $fieldManager, $currentValues) {
        try {
            // Обработка значения поля с учетом текущих значений
            $value = $fieldManager->processFieldValue($field, $_POST, $_FILES, $currentValues);
            
            if ($value !== null) {
                // Декодирование конфигурации поля
                $config = is_array($field['config']) 
                    ? $field['config'] 
                    : json_decode($field['config'] ?? '{}', true);
                
                // Сохранение значения поля
                $fieldModel->saveFieldValue(
                    $field['id'], 
                    'page', 
                    $this->id, 
                    $value,
                    $field['type'],
                    $config
                );
            }
        } catch (\Exception $e) {
            // Уведомление об ошибке для конкретного поля
            \Notification::error("Ошибка обработки поля {$field['name']}: " . $e->getMessage());
        }
    }
    
    /**
     * Обрабатывает успешное обновление страницы
     */
    private function handleUpdateSuccess() {
        // Для AJAX-запросов возвращаем JSON с перенаправлением
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(true, 'Страница успешно обновлена', [
                'redirect' => ADMIN_URL . '/pages'
            ]);
            exit;
        }
        
        // Для обычных запросов показываем уведомление и перенаправляем
        \Notification::success('Страница успешно обновлена');
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
     * Обрабатывает ошибку при обновлении страницы
     * 
     * @param \Exception $e Исключение
     */
    private function handleUpdateError($e) {
        // Для AJAX-запросов возвращаем JSON с ошибкой
        if ($this->isAjaxRequest()) {
            $this->sendJsonResponse(false, $e->getMessage());
            exit;
        }
        
        // Для обычных запросов показываем уведомление об ошибке
        \Notification::error('Ошибка при обновлении страницы: ' . $e->getMessage());
    }
    
    /**
     * Отправляет JSON-ответ для AJAX-запросов
     * 
     * @param bool $success Флаг успеха
     * @param string $message Сообщение
     * @param array $extra Дополнительные данные
     */
    private function sendJsonResponse($success, $message, $extra = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(
            ['success' => $success, 'message' => $message],
            $extra
        ));
    }
    
    /**
     * Отображает форму редактирования страницы
     * 
     * @param array $page Данные страницы
     * @param array $preparedBlocks Подготовленные блоки
     */
    private function renderEditForm($page, $preparedBlocks) {
        $this->render('admin/pages/edit', [
            'page' => $page,
            'preparedBlocks' => $preparedBlocks,
            'postBlockManager' => $this->postBlockManager,
            'pageTitle' => 'Редактирование страницы'
        ]);
    }
    
    /**
     * Обрабатывает ошибку при загрузке страницы
     * 
     * @param \Exception $e Исключение
     */
    private function handleLoadError($e) {
        \Notification::error('Ошибка при загрузке страницы: ' . $e->getMessage());
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
     * Обрабатывает ситуацию с отсутствием прав доступа
     */
    private function handleAccessDenied() {
        \Notification::error('У вас нет прав доступа к этому разделу');
        $this->redirect(ADMIN_URL . '/login');
    }
}