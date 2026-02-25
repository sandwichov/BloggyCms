<?php

namespace users\actions;

/**
 * Действие создания нового пользователя в административной панели
 * Отображает форму создания пользователя и обрабатывает её отправку,
 * включая валидацию, загрузку аватара, пользовательские поля,
 * назначение групп и ручных достижений
 * 
 * @package users\actions
 * @extends UserAction
 */
class AdminCreate extends UserAction {
    
    /**
     * Метод выполнения создания пользователя
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
     * Обрабатывает POST-запрос на создание пользователя
     * 
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest() {
        // Валидация обязательных полей
        $this->validateRequiredFields();
        
        // Проверка уникальности
        $this->checkUniqueness();
        
        // Подготовка основных данных пользователя
        $userData = $this->prepareUserData();
        
        // Обработка загрузки аватара
        $userData = $this->handleAvatarUpload($userData);
        
        // Создание пользователя в базе данных
        $userId = $this->userModel->create($userData);
        
        // Сохранение пользовательских полей
        $this->saveCustomFields($userId);
        
        // Назначение групп
        $this->assignUserGroups($userId);
        
        // Назначение ручных достижений
        $this->assignAchievements($userId);
        
        // Уведомление об успехе и перенаправление
        \Notification::success('Пользователь успешно создан');
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
     * Валидирует обязательные поля формы
     * 
     * @throws \Exception При ошибках валидации
     * @return void
     */
    private function validateRequiredFields() {
        if (empty($_POST['username'])) {
            throw new \Exception('Имя пользователя обязательно');
        }

        if (empty($_POST['email'])) {
            throw new \Exception('Email обязателен');
        }

        if (empty($_POST['password'])) {
            throw new \Exception('Пароль обязателен');
        }

        if ($_POST['password'] !== $_POST['password_confirm']) {
            throw new \Exception('Пароли не совпадают');
        }
    }
    
    /**
     * Проверяет уникальность имени пользователя и email
     * 
     * @throws \Exception Если пользователь уже существует
     * @return void
     */
    private function checkUniqueness() {
        if ($this->userModel->getByUsername($_POST['username'])) {
            throw new \Exception('Пользователь с таким именем уже существует');
        }

        if ($this->userModel->getByEmail($_POST['email'])) {
            throw new \Exception('Пользователь с таким email уже существует');
        }
    }
    
    /**
     * Подготавливает основные данные пользователя из POST
     * 
     * @return array Массив с данными пользователя
     */
    private function prepareUserData() {
        return [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'display_name' => $_POST['display_name'] ?? null,
            'bio' => $_POST['bio'] ?? null,
            'website' => $_POST['website'] ?? null,
            'role' => $_POST['role'] ?? 'user',
            'status' => $_POST['status'] ?? 'active'
        ];
    }
    
    /**
     * Обрабатывает загрузку аватара пользователя
     * 
     * @param array $userData Данные пользователя
     * @return array Обновленные данные пользователя
     */
    private function handleAvatarUpload($userData) {
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $uploadDir = UPLOADS_PATH . '/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                $userData['avatar'] = $fileName;
            }
        }
        
        return $userData;
    }
    
    /**
     * Сохраняет пользовательские поля для нового пользователя
     * 
     * @param int $userId ID созданного пользователя
     * @return void
     */
    private function saveCustomFields($userId) {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        $currentValues = [];
        $fieldManager = new \FieldManager($this->db);
        
        foreach ($customFields as $field) {
            try {
                $value = $fieldManager->processFieldValue(
                    $field, 
                    $_POST, 
                    $_FILES,
                    $currentValues
                );
                
                if ($value !== null) {
                    $this->fieldModel->saveFieldValue(
                        $field['id'],
                        'user',
                        $userId,
                        $value,
                        $field['type'],
                        $field['config']
                    );
                }
            } catch (\Exception $e) {
                \Notification::error("Ошибка при сохранении поля {$field['name']}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Назначает пользователю группы
     * 
     * @param int $userId ID пользователя
     * @return void
     */
    private function assignUserGroups($userId) {
        if (!empty($_POST['groups'])) {
            $this->userModel->updateUserGroups($userId, $_POST['groups']);
        } else {
            $defaultGroup = $this->userModel->getDefaultGroup();
            if ($defaultGroup) {
                $this->userModel->updateUserGroups($userId, [$defaultGroup['id']]);
            }
        }
    }
    
    /**
     * Назначает пользователю ручные достижения
     * 
     * @param int $userId ID пользователя
     * @return void
     */
    private function assignAchievements($userId) {
        if (!empty($_POST['achievements'])) {
            foreach ($_POST['achievements'] as $achievementId) {
                // Проверка, что ачивка ручная
                $achievement = $this->userModel->getAchievementById($achievementId);
                if ($achievement && $achievement['type'] == 'manual') {
                    $this->userModel->assignAchievementToUser($userId, $achievementId);
                }
            }
        }
    }
    
    /**
     * Отображает форму создания пользователя
     * 
     * @return void
     */
    private function renderCreateForm() {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/create', [
            'customFields' => $customFields,
            'pageTitle' => 'Создание пользователя'
        ]);
    }
    
    /**
     * Обрабатывает ошибку при создании пользователя
     * 
     * @param \Exception $e Исключение
     * @return void
     */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/create', [
            'user' => $_POST,
            'customFields' => $customFields,
            'pageTitle' => 'Создание пользователя'
        ]);
    }
}