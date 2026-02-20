<?php

namespace categories\actions;

/**
 * Действие проверки пароля для защищенных категорий
 * Обрабатывает AJAX-запросы на проверку пароля категории и устанавливает сессионный доступ
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class CheckPassword extends CategoryAction {
    
    /**
     * Метод выполнения проверки пароля
     * Верифицирует пароль для доступа к защищенной категории и сохраняет доступ в сессии
     * Все ответы возвращаются в формате JSON
     * 
     * @return void
     */
    public function execute() {
        // Получение ID категории из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID категории
        if (!$id) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'ID категории не указан'
            ]);
            return;
        }

        // Проверка метода запроса (допустим только POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Метод не поддерживается'
            ]);
            return;
        }
        
        try {
            // Получение пароля из POST-данных
            $password = $_POST['password'] ?? '';
            
            // Получение данных категории
            $category = $this->categoryModel->getById($id);
            
            // Проверка существования категории
            if (!$category) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Категория не найдена'
                ]);
                return;
            }
            
            // Проверка пароля или отсутствия защиты
            if (!$category['password_protected'] || $category['password'] === $password) {
                // Инициализация массива доступа к категориям в сессии
                if (!isset($_SESSION['category_access'])) {
                    $_SESSION['category_access'] = [];
                }
                
                // Сохранение успешного доступа в сессии
                $_SESSION['category_access'][$category['id']] = true;
                
                // Успешный ответ
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
            } else {
                // Ответ при неверном пароле
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Неверный пароль'
                ]);
            }
            
        } catch (\Exception $e) {
            // Обработка исключений
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Ошибка при проверке пароля'
            ]);
        }
    }
}