<?php

namespace users\actions\achievements;

/**
 * Действие редактирования достижения (ачивки) в административной панели
 * Отображает форму редактирования существующей ачивки и обрабатывает её отправку,
 * включая обновление условий, загрузку/удаление изображения и сохранение в БД
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementEdit extends AdminAchievementAction {
    
    /**
     * Метод выполнения редактирования ачивки
     * Проверяет ID, загружает данные ачивки, обрабатывает POST-запрос для сохранения
     * или отображает форму с текущими данными
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение ID ачивки из параметров
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID ачивки не указан');
            }
            
            // Загрузка данных ачивки
            $achievement = $this->userModel->getAchievementById($id);
            if (!$achievement) {
                throw new \Exception('Ачивка не найдена');
            }
            
            // Обработка POST-запроса (сохранение изменений)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $achievement);
                return;
            }
            
            // Отображение формы редактирования
            $this->renderEditForm($achievement);
            
        } catch (\Exception $e) {
            // Обработка ошибок
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/user-achievements');
        }
    }
    
    /**
     * Обрабатывает POST-запрос на обновление ачивки
     * 
     * @param int $id ID ачивки
     * @param array $achievement Текущие данные ачивки
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest($id, $achievement) {
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
        
        // Обработка изображения (загрузка нового)
        $imageName = $this->handleImageUpload($achievement);
        if ($imageName) {
            $achievementData['image'] = $imageName;
        }
        
        // Обработка удаления изображения
        if (isset($_POST['remove_image']) && $_POST['remove_image']) {
            $this->handleImageDelete($achievement);
            $achievementData['image'] = null;
        }
        
        // Обновление ачивки в базе данных
        $this->userModel->updateAchievement($id, $achievementData);
        
        // Уведомление об успехе и перенаправление
        \Notification::success('Ачивка успешно обновлена');
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
     * Обрабатывает загрузку нового изображения для ачивки
     * Удаляет старое изображение при успешной загрузке нового
     * 
     * @param array $achievement Текущие данные ачивки
     * @return string|null Имя загруженного файла или null
     */
    private function handleImageUpload($achievement) {
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
            // Удаление старого изображения, если есть
            if (!empty($achievement['image'])) {
                $oldImage = $uploadDir . $achievement['image'];
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
            }
            return $fileName;
        }
        
        return null;
    }
    
    /**
     * Обрабатывает удаление изображения ачивки
     * 
     * @param array $achievement Текущие данные ачивки
     * @return void
     */
    private function handleImageDelete($achievement) {
        if (!empty($achievement['image'])) {
            $uploadDir = UPLOADS_PATH . '/achievements/';
            $oldImage = $uploadDir . $achievement['image'];
            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }
    }
    
    /**
     * Отображает форму редактирования ачивки
     * 
     * @param array $achievement Данные ачивки
     * @return void
     */
    private function renderEditForm($achievement) {
        $this->render('admin/user-achievements/edit', [
            'achievement' => $achievement,
            'pageTitle' => 'Редактирование ачивки'
        ]);
    }
}