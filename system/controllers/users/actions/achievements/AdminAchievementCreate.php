<?php

namespace users\actions\achievements;

/**
 * Действие создания нового достижения (ачивки) в административной панели
 * Отображает форму создания ачивки и обрабатывает её отправку,
 * включая настройку условий, загрузку изображения и сохранение в БД
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementCreate extends AdminAchievementAction {
    
    /**
     * Метод выполнения создания ачивки
     * При GET-запросе отображает форму, при POST-запросе обрабатывает сохранение
     * 
     * @return void
     */
    public function execute() {
        try {
            // Обработка POST-запроса (отправка формы)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
                return;
            }
            
            // Отображение формы создания
            $this->renderCreateForm();
            
        } catch (\Exception $e) {
            // Обработка ошибок
            $this->handleError($e);
        }
    }
    
    /**
     * Обрабатывает POST-запрос на создание ачивки
     * 
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest() {
        // Валидация обязательного поля
        if (empty($_POST['name'])) {
            throw new \Exception('Название ачивки обязательно');
        }
        
        // Подготовка условий
        $conditions = $this->prepareConditions();
        
        // Подготовка основных данных ачивки
        $achievementData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'icon' => $_POST['icon'] ?? 'trophy',
            'icon_color' => $_POST['icon_color'] ?? '#0088cc',
            'type' => $_POST['type'] ?? 'auto',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'priority' => $_POST['priority'] ?? 0,
            'conditions' => $conditions
        ];
        
        // Загрузка изображения (если есть)
        $imageName = $this->handleImageUpload();
        if ($imageName) {
            $achievementData['image'] = $imageName;
        }
        
        // Создание ачивки в базе данных
        $achievementId = $this->userModel->createAchievement($achievementData);
        
        // Уведомление об успехе и перенаправление
        \Notification::success('Ачивка успешно создана');
        $this->redirect(ADMIN_URL . '/user-achievements');
    }
    
    /**
     * Подготавливает массив условий из POST-данных
     * Фильтрует пустые и некорректные условия
     * 
     * @return array Массив условий для ачивки
     */
    private function prepareConditions() {
        $conditions = [];
        
        if (!empty($_POST['conditions'])) {
            foreach ($_POST['conditions'] as $condition) {
                // Проверка наличия всех необходимых полей
                if (!empty($condition['type']) && !empty($condition['operator']) && isset($condition['value'])) {
                    $conditions[] = [
                        'type' => $condition['type'],
                        'operator' => $condition['operator'],
                        'value' => $condition['value']
                    ];
                }
            }
        }
        
        return $conditions;
    }
    
    /**
     * Обрабатывает загрузку изображения для ачивки
     * 
     * @return string|null Имя загруженного файла или null
     */
    private function handleImageUpload() {
        if (empty($_FILES['image']['tmp_name'])) {
            return null;
        }
        
        // Создание директории для ачивок, если не существует
        $uploadDir = UPLOADS_PATH . '/achievements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Генерация уникального имени файла
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Сохранение файла
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            return $fileName;
        }
        
        return null;
    }
    
    /**
     * Отображает форму создания ачивки
     * 
     * @return void
     */
    private function renderCreateForm() {
        $this->render('admin/user-achievements/create', [
            'pageTitle' => 'Создание ачивки'
        ]);
    }
    
    /**
     * Обрабатывает ошибку при создании ачивки
     * 
     * @param \Exception $e Исключение
     * @return void
     */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        
        // Повторное отображение формы с введенными данными
        $this->render('admin/user-achievements/create', [
            'achievement' => $_POST,
            'pageTitle' => 'Создание ачивки'
        ]);
    }
}