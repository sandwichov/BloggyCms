<?php

namespace users\actions;

/**
 * Действие редактирования пользователя в административной панели
 * Отображает форму редактирования существующего пользователя и обрабатывает её отправку,
 * включая валидацию, обновление данных, загрузку/удаление аватара,
 * пользовательские поля, группы и ручные достижения
 * 
 * @package users\actions
 * @extends UserAction
 */
class AdminEdit extends UserAction {
    
    /**
     * Метод выполнения редактирования пользователя
     * Проверяет ID, загружает данные пользователя, обрабатывает POST-запрос
     * или отображает форму с текущими данными
     * 
     * @return void
     */
    public function execute() {
        // Получение ID пользователя из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID
        if (!$id) {
            \Notification::error('ID пользователя не указан');
            $this->redirect(ADMIN_URL . '/users');
            return;
        }
        
        try {
            // Загрузка данных пользователя
            $user = $this->loadUser($id);
            
            // Обработка POST-запроса (сохранение изменений)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $user);
                return;
            }

            // Отображение формы редактирования
            $this->renderEditForm($user);

        } catch (\Exception $e) {
            // Обработка ошибок
            $this->handleError($e, $id);
        }
    }
    
    /**
     * Загружает пользователя по ID и проверяет существование
     * 
     * @param int $id ID пользователя
     * @return array Данные пользователя
     * @throws \Exception Если пользователь не найден
     */
    private function loadUser($id) {
        $user = $this->userModel->getById($id);
        if (!$user) {
            throw new \Exception('Пользователь не найден');
        }
        return $user;
    }
    
    /**
     * Обрабатывает POST-запрос на обновление пользователя
     * 
     * @param int $id ID пользователя
     * @param array $user Текущие данные пользователя
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest($id, $user) {
        // Валидация обязательных полей
        $this->validateRequiredFields();
        
        // Проверка уникальности (исключая текущего пользователя)
        $this->checkUniqueness($id);
        
        // Подготовка основных данных
        $userData = $this->prepareUserData($user);
        
        // Обработка пароля (если указан)
        $userData = $this->handlePasswordChange($userData);
        
        // Обработка аватара (загрузка/удаление)
        $userData = $this->handleAvatarUpdate($userData, $user);
        
        // Сохранение пользовательских полей
        $this->saveCustomFields($id);
        
        // Обновление групп
        $this->updateUserGroups($id);
        
        // Обновление ручных достижений
        $this->updateUserAchievements($id);
        
        // Обновление основных данных пользователя
        $this->userModel->update($id, $userData);

        \Notification::success('Пользователь успешно обновлен');
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
     * Валидирует обязательные поля формы
     * 
     * @throws \Exception При ошибках валидации
     */
    private function validateRequiredFields() {
        if (empty($_POST['username'])) {
            throw new \Exception('Имя пользователя обязательно');
        }

        if (empty($_POST['email'])) {
            throw new \Exception('Email обязателен');
        }
    }
    
    /**
     * Проверяет уникальность имени пользователя и email
     * 
     * @param int $id ID текущего пользователя для исключения
     * @throws \Exception Если данные уже заняты
     */
    private function checkUniqueness($id) {
        $existingUser = $this->userModel->getByUsername($_POST['username']);
        if ($existingUser && $existingUser['id'] != $id) {
            throw new \Exception('Пользователь с таким именем уже существует');
        }

        $existingUser = $this->userModel->getByEmail($_POST['email']);
        if ($existingUser && $existingUser['id'] != $id) {
            throw new \Exception('Пользователь с таким email уже существует');
        }
    }
    
    /**
     * Подготавливает основные данные пользователя
     * 
     * @param array $user Текущие данные пользователя
     * @return array Данные для обновления
     */
    private function prepareUserData($user) {
        return [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'display_name' => $_POST['display_name'] ?? null,
            'website' => $_POST['website'] ?? null,
            'bio' => $_POST['bio'] ?? null,
            'role' => $_POST['role'] ?? 'user',
            'status' => $_POST['status'] ?? 'active'
        ];
    }
    
    /**
     * Обрабатывает смену пароля
     * 
     * @param array $userData Данные пользователя
     * @return array Обновленные данные
     * @throws \Exception Если пароли не совпадают
     */
    private function handlePasswordChange($userData) {
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['password_confirm']) {
                throw new \Exception('Пароли не совпадают');
            }
            $userData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        return $userData;
    }
    
    /**
     * Обрабатывает обновление аватара (загрузку или удаление)
     * 
     * @param array $userData Данные пользователя
     * @param array $user Текущие данные пользователя
     * @return array Обновленные данные
     */
    private function handleAvatarUpdate($userData, $user) {
        // Загрузка нового аватара
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $uploadDir = UPLOADS_PATH . '/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['avatar']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $targetPath)) {
                $this->deleteOldAvatar($user);
                $userData['avatar'] = $fileName;
            }
        }
        
        // Удаление текущего аватара
        if (isset($_POST['remove_avatar']) && $_POST['remove_avatar']) {
            $this->deleteOldAvatar($user);
            $userData['avatar'] = 'default.jpg';
        }
        
        return $userData;
    }
    
    /**
     * Удаляет старый аватар пользователя
     * 
     * @param array $user Данные пользователя
     */
    private function deleteOldAvatar($user) {
        if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
            $avatarPath = UPLOADS_PATH . '/avatars/' . $user['avatar'];
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
        }
    }
    
    /**
     * Сохраняет пользовательские поля
     * 
     * @param int $userId ID пользователя
     */
    private function saveCustomFields($userId) {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        foreach ($customFields as $field) {
            $fieldKey = 'field_' . $field['system_name'];
            
            if (isset($_POST[$fieldKey])) {
                $value = $_POST[$fieldKey];
                
                $this->fieldModel->saveFieldValue(
                    $field['id'],
                    'user',
                    $userId,
                    $value,
                    $field['type'],
                    $field['config']
                );
            }
        }
    }
    
    /**
     * Обновляет группы пользователя
     * 
     * @param int $userId ID пользователя
     */
    private function updateUserGroups($userId) {
        if (isset($_POST['groups'])) {
            $this->userModel->updateUserGroups($userId, $_POST['groups']);
        } else {
            $this->userModel->updateUserGroups($userId, []);
        }
    }
    
    /**
     * Обновляет ручные достижения пользователя
     * 
     * @param int $userId ID пользователя
     */
    private function updateUserAchievements($userId) {
        if (!isset($_POST['achievements'])) {
            return;
        }
        
        // Получение текущих ачивок пользователя
        $currentAchievements = $this->userModel->getUserUnlockedAchievements($userId);
        $currentAchievementIds = array_column($currentAchievements, 'id');
        
        // Разблокировка новых ачивок
        foreach ($_POST['achievements'] as $achievementId) {
            if (!in_array($achievementId, $currentAchievementIds)) {
                $achievement = $this->userModel->getAchievementById($achievementId);
                if ($achievement && $achievement['type'] == 'manual') {
                    $this->userModel->assignAchievementToUser($userId, $achievementId);
                }
            }
        }
        
        // Блокировка удаленных ачивок (только ручные)
        foreach ($currentAchievements as $achievement) {
            if ($achievement['type'] == 'manual' && !in_array($achievement['id'], $_POST['achievements'])) {
                $this->userModel->removeAchievementFromUser($userId, $achievement['id']);
            }
        }
    }
    
    /**
     * Отображает форму редактирования пользователя
     * 
     * @param array $user Данные пользователя
     */
    private function renderEditForm($user) {
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/edit', [
            'user' => $user,
            'customFields' => $customFields,
            'pageTitle' => 'Редактирование пользователя'
        ]);
    }
    
    /**
     * Обрабатывает ошибку при редактировании
     * 
     * @param \Exception $e Исключение
     * @param int $id ID пользователя
     */
    private function handleError($e, $id) {
        \Notification::error($e->getMessage());
        
        $user = $this->userModel->getById($id);
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        $this->render('admin/users/edit', [
            'user' => array_merge($user, $_POST),
            'customFields' => $customFields,
            'pageTitle' => 'Редактирование пользователя'
        ]);
    }
}