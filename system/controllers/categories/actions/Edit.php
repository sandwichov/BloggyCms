<?php

namespace categories\actions;

/**
 * Действие редактирования существующей категории
 * Обрабатывает форму редактирования категории с обновлением данных, изображений и кастомных полей
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class Edit extends CategoryAction {
    
    /**
     * @var string Заголовок страницы
     */
    private $pageTitle = 'Редактирование категории';
    
    /**
     * Метод выполнения редактирования категории
     * Обрабатывает форму обновления данных категории, включая управление изображениями
     * 
     * @return void
     */
    public function execute() {
        // Получение ID категории из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID категории
        if (!$id) {
            \Notification::error('ID категории не указан');
            $this->redirect(ADMIN_URL . '/categories');
            return;
        }

        // Установка заголовка страницы
        $this->pageTitle = 'Редактирование категории';
        
        try {
            // Получение данных категории из базы данных
            $category = $this->categoryModel->getById($id);
            
            // Проверка существования категории
            if (!$category) {
                \Notification::error('Категория не найдена');
                $this->redirect(ADMIN_URL . '/categories');
                return;
            }
            
            // Обработка POST-запроса (отправка формы редактирования)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    // Подготовка данных для обновления из формы
                    $data = [
                        'name' => trim($_POST['name']),
                        'slug' => trim($_POST['slug'] ?? ''),
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
                    
                    // Блок обработки изображения категории
                    
                    // Вариант 1: Загрузка нового изображения
                    if (!empty($_FILES['image']['name'])) {
                        $uploadDir = UPLOADS_PATH . '/images/categories';
                        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $maxSize = 5120; // 5MB
                        
                        // Загрузка нового изображения
                        $fileName = \FileUpload::upload($_FILES['image'], $uploadDir, $allowedTypes, $maxSize);
                        $data['image'] = 'categories/' . $fileName;
                        
                        // Удаление старого изображения если оно существовало
                        if (!empty($category['image'])) {
                            $oldImagePath = UPLOADS_PATH . '/images/' . $category['image'];
                            \FileUpload::delete($oldImagePath);
                        }
                    } 
                    // Вариант 2: Удаление существующего изображения по запросу
                    elseif (isset($_POST['delete_image']) && $_POST['delete_image']) {
                        // Удаление изображения если отмечен чекбокс
                        if (!empty($category['image'])) {
                            $oldImagePath = UPLOADS_PATH . '/images/' . $category['image'];
                            \FileUpload::delete($oldImagePath);
                        }
                        $data['image'] = ''; // Очистка пути к изображению
                    } 
                    // Вариант 3: Сохранение существующего изображения
                    else {
                        $data['image'] = $category['image'] ?? '';
                    }
                    
                    // ВАЖНО: Очистка пароля при отключении защиты
                    // Если защита паролем отключена, пароль устанавливается в null
                    if (!isset($_POST['password_protected']) || !$_POST['password_protected']) {
                        $data['password'] = null;
                    }
                    
                    // Обновление категории в базе данных
                    $result = $this->categoryModel->update($id, $data);
                    
                    if (!$result) {
                        throw new \Exception('Не удалось обновить категорию');
                    }
                    
                    // Блок обработки кастомных полей
                    $fieldModel = new \FieldModel($this->db);
                    $fieldManager = new \FieldManager($this->db);
                    
                    // Получение активных кастомных полей для категорий
                    $customFields = $fieldModel->getActiveByEntityType('category');
                    
                    // Получение текущих значений кастомных полей для сравнения
                    $currentValues = [];
                    foreach ($customFields as $field) {
                        $currentValues[$field['system_name']] = $fieldModel->getFieldValue(
                            'category', 
                            $id, 
                            $field['system_name']
                        );
                    }

                    // Обновление значений кастомных полей
                    foreach ($customFields as $field) {
                        try {
                            // Обработка значения поля с учетом текущих данных
                            $value = $fieldManager->processFieldValue(
                                $field, 
                                $_POST, 
                                $_FILES, 
                                $currentValues
                            );
                            
                            // Сохранение значения если оно было изменено
                            if ($value !== null) {
                                $config = is_array($field['config']) 
                                    ? $field['config'] 
                                    : json_decode($field['config'] ?? '{}', true);
                                
                                $fieldModel->saveFieldValue(
                                    $field['id'], 
                                    'category', 
                                    $id, 
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
                    
                    // Уведомление об успехе и редирект
                    \Notification::success('Категория успешно обновлена');
                    $this->redirect(ADMIN_URL . '/categories');
                    return;
                    
                } catch (\Exception $e) {
                    // Обработка ошибок при обновлении категории
                    \Notification::error('Ошибка при обновлении категории: ' . $e->getMessage());
                    
                    // Перезагрузка данных категории для отображения формы с ошибками
                    $category = $this->categoryModel->getById($id);
                    $this->render('admin/categories/form', [
                        'category' => $category, 
                        'data' => array_merge($category, $_POST),
                        'pageTitle' => $this->pageTitle
                    ]);
                    return;
                }
            }
            
            // Рендеринг формы для GET-запроса с текущими данными категории
            $this->render('admin/categories/form', [
                'category' => $category,
                'pageTitle' => $this->pageTitle
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке категории
            \Notification::error('Ошибка при загрузке категории: ' . $e->getMessage());
            $this->redirect(ADMIN_URL . '/categories');
        }
    }
}