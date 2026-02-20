<?php

namespace pages\actions;

/**
 * Действие создания новой страницы в административной панели
 * Отображает форму создания страницы, обрабатывает её отправку,
 * сохраняет страницу, её блоки и пользовательские поля
 * 
 * @package pages\actions
 * @extends PageAction
 */
class AdminCreate extends PageAction {
    
    /**
     * Метод выполнения создания страницы
     * Проверяет права доступа, загружает ассеты блоков, обрабатывает POST-запрос
     * и сохраняет данные страницы вместе с блоками и пользовательскими полями
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            $this->handleAccessDenied();
            return;
        }
        
        // Загрузка всех ассетов (CSS/JS) для блоков
        $this->postBlockManager->loadAllPostBlockAssets();
        
        // Обработка POST-запроса (отправка формы)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        } else {
            // Отображение пустой формы создания
            $this->renderCreateForm();
        }
    }
    
    /**
     * Обрабатывает POST-запрос на создание страницы
     * Валидирует данные, создает страницу, сохраняет блоки и пользовательские поля
     * 
     * @return void
     */
    private function handlePostRequest() {
        try {
            // Валидация обязательных полей
            $this->validateRequiredFields();
            
            // Подготовка данных страницы
            $data = $this->preparePageData();
            
            // Создание страницы в базе данных
            $pageId = $this->pageModel->create($data);
            
            // Обработка и сохранение блоков страницы
            $this->processPageBlocksFromPost($pageId);
            
            // Обработка и сохранение пользовательских полей
            $this->processCustomFields($pageId);
            
            // Уведомление об успехе и перенаправление
            $this->handleSuccess();
            
        } catch (\Exception $e) {
            // Обработка ошибок с сохранением введенных данных
            $this->handleError($e);
        }
    }
    
    /**
     * Проверяет обязательные поля формы
     * 
     * @throws \Exception Если обязательные поля не заполнены
     * @return void
     */
    private function validateRequiredFields() {
        if (empty($_POST['title'])) {
            throw new \Exception('Заголовок страницы обязателен для заполнения');
        }
    }
    
    /**
     * Подготавливает данные страницы из POST-запроса
     * 
     * @return array Массив с данными страницы
     */
    private function preparePageData() {
        return [
            'title' => $_POST['title'],
            'status' => $_POST['status'] ?? 'draft'
        ];
    }
    
    /**
     * Обрабатывает и сохраняет блоки страницы из POST-запроса
     * 
     * @param int $pageId ID созданной страницы
     * @throws \Exception При неверном формате данных блоков
     * @return void
     */
    private function processPageBlocksFromPost($pageId) {
        if (empty($_POST['post_blocks'])) {
            return;
        }
        
        $blocksData = json_decode($_POST['post_blocks'], true);
        
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($blocksData)) {
            throw new \Exception('Неверный формат данных блоков');
        }
        
        $this->processPageBlocks($pageId, $blocksData);
    }
    
    /**
     * Обрабатывает и сохраняет пользовательские поля для страницы
     * 
     * @param int $pageId ID созданной страницы
     * @return void
     */
    private function processCustomFields($pageId) {
        $fieldModel = new \FieldModel($this->db);
        $fieldManager = new \FieldManager($this->db);
        
        // Получение активных полей для страниц
        $customFields = $fieldModel->getActiveByEntityType('page');
        
        foreach ($customFields as $field) {
            $this->processSingleCustomField($field, $pageId, $fieldModel, $fieldManager);
        }
    }
    
    /**
     * Обрабатывает одно пользовательское поле
     * 
     * @param array $field Данные поля
     * @param int $pageId ID страницы
     * @param \FieldModel $fieldModel Модель полей
     * @param \FieldManager $fieldManager Менеджер полей
     * @return void
     */
    private function processSingleCustomField($field, $pageId, $fieldModel, $fieldManager) {
        try {
            // Обработка значения поля из POST и FILES
            $value = $fieldManager->processFieldValue($field, $_POST, $_FILES);
            
            if ($value !== null) {
                // Декодирование конфигурации поля
                $config = is_array($field['config']) 
                    ? $field['config'] 
                    : json_decode($field['config'] ?? '{}', true);
                
                // Сохранение значения поля
                $fieldModel->saveFieldValue(
                    $field['id'], 
                    'page', 
                    $pageId, 
                    $value,
                    $field['type'],
                    $config
                );
            }
        } catch (\Exception $e) {
            // Уведомление об ошибке для конкретного поля, но продолжение обработки
            \Notification::error("Ошибка обработки поля {$field['name']}: " . $e->getMessage());
        }
    }
    
    /**
     * Обрабатывает успешное создание страницы
     * 
     * @return void
     */
    private function handleSuccess() {
        \Notification::success('Страница успешно создана');
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
     * Обрабатывает ошибку при создании страницы
     * Сохраняет введенные данные и отображает форму с ними
     * 
     * @param \Exception $e Исключение с сообщением об ошибке
     * @return void
     */
    private function handleError($e) {
        \Notification::error('Ошибка при создании страницы: ' . $e->getMessage());
        
        // Подготовка данных блоков для повторного отображения
        $preparedBlocks = $this->prepareBlocksFromPost();
        
        // Отображение формы с введенными данными
        $this->render('admin/pages/create', [
            'data' => $_POST,
            'preparedBlocks' => $preparedBlocks,
            'postBlockManager' => $this->postBlockManager,
            'pageTitle' => 'Создание страницы'
        ]);
    }
    
    /**
     * Подготавливает данные блоков из POST-запроса для повторного отображения
     * 
     * @return array Массив подготовленных блоков
     */
    private function prepareBlocksFromPost() {
        $preparedBlocks = [];
        
        if (!empty($_POST['post_blocks'])) {
            $blocksData = json_decode($_POST['post_blocks'], true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_array($blocksData)) {
                foreach ($blocksData as $index => $block) {
                    $preparedBlocks[] = [
                        'id' => $block['id'] ?? 'block_' . $index,
                        'type' => $block['type'] ?? '',
                        'content' => $block['content'] ?? [],
                        'settings' => $block['settings'] ?? [],
                        'order' => (int)($block['order'] ?? $index)
                    ];
                }
            }
        }
        
        return $preparedBlocks;
    }
    
    /**
     * Отображает пустую форму создания страницы
     * 
     * @return void
     */
    private function renderCreateForm() {
        $this->render('admin/pages/create', [
            'postBlockManager' => $this->postBlockManager,
            'pageTitle' => 'Создание страницы'
        ]);
    }
    
    /**
     * Обрабатывает ситуацию с отсутствием прав доступа
     * 
     * @return void
     */
    private function handleAccessDenied() {
        \Notification::error('У вас нет прав доступа к этому разделу');
        $this->redirect(ADMIN_URL . '/login');
    }
}