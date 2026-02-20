<?php

namespace categories\actions;

/**
 * Действие создания новой категории
 * Обрабатывает форму создания категории, включая загрузку изображений и работу с кастомными полями
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class Create extends CategoryAction {
    
    /**
     * @var string Заголовок страницы
     */
    private $pageTitle = 'Создание категории';
    
    /**
     * Метод выполнения создания категории
     * Обрабатывает POST-запросы для создания категории, включая загрузку файлов и кастомные поля
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка страницы
        $this->pageTitle = 'Создание категории';
        
        // Обработка POST-запроса (отправка формы)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Подготовка данных категории из формы
                $data = [
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description'] ?? ''),
                    'meta_title' => trim($_POST['meta_title'] ?? ''),
                    'meta_description' => trim($_POST['meta_description'] ?? ''),
                    'canonical_url' => trim($_POST['canonical_url'] ?? ''),
                    'noindex' => isset($_POST['noindex']) ? 1 : 0,
                    'sort_order' => (int)($_POST['sort_order'] ?? 0),
                    'password_protected' => isset($_POST['password_protected']) ? 1 : 0,
                    'password' => isset($_POST['password_protected']) && !empty($_POST['password']) 
                        ? trim($_POST['password']) 
                        : null
                ];
                
                // Обработка загрузки изображения категории
                if (!empty($_FILES['image']['name'])) {
                    $uploadDir = UPLOADS_PATH . '/images/categories';
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    $maxSize = 5120; // 5MB в килобайтах
                    
                    // Загрузка изображения через утилиту FileUpload
                    $fileName = \FileUpload::upload($_FILES['image'], $uploadDir, $allowedTypes, $maxSize);
                    $data['image'] = 'categories/' . $fileName; // Сохранение относительного пути
                } else {
                    $data['image'] = ''; // Пустое значение если изображение не загружено
                }
                
                // Создание категории в базе данных
                $categoryId = $this->categoryModel->create($data);
                
                // Обработка кастомных полей категории
                $fieldModel = new \FieldModel($this->db);
                $fieldManager = new \FieldManager($this->db);
                
                // Получение активных кастомных полей для сущности "category"
                $customFields = $fieldModel->getActiveByEntityType('category');
                
                // Обработка каждого кастомного поля
                foreach ($customFields as $field) {
                    try {
                        // Обработка значения поля (текст, файл и т.д.)
                        $value = $fieldManager->processFieldValue($field, $_POST, $_FILES);
                        
                        // Сохранение значения если оно было передано
                        if ($value !== null) {
                            // Парсинг конфигурации поля
                            $config = is_array($field['config']) 
                                ? $field['config'] 
                                : json_decode($field['config'] ?? '{}', true);
                            
                            // Сохранение значения поля в базе данных
                            $fieldModel->saveFieldValue(
                                $field['id'], 
                                'category', 
                                $categoryId, 
                                $value,
                                $field['type'],
                                $config
                            );
                        }
                    } catch (\Exception $e) {
                        // Логирование ошибок обработки отдельных полей
                        \Notification::error("Ошибка обработки поля {$field['name']}: " . $e->getMessage());
                    }
                }
                
                // Уведомление об успешном создании и редирект
                \Notification::success('Категория успешно создана');
                $this->redirect(ADMIN_URL . '/categories');
                return;
                
            } catch (\Exception $e) {
                // Обработка исключений при создании категории
                \Notification::error('Ошибка при создании категории: ' . $e->getMessage());
                
                // Повторный рендеринг формы с сохраненными данными
                $this->render('admin/categories/form', [
                    'data' => $_POST, // Передача заполненных данных обратно в форму
                    'pageTitle' => $this->pageTitle
                ]);
                return;
            }
        }
        
        // Рендеринг пустой формы для GET-запроса
        $this->render('admin/categories/form', [
            'pageTitle' => $this->pageTitle
        ]);
    }
}