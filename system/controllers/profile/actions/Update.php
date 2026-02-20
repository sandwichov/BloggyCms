<?php

namespace profile\actions;

/**
 * Действие обновления данных профиля пользователя
 * Обрабатывает POST-запрос с формой редактирования профиля,
 * проверяет CSRF-токен, валидирует и сохраняет данные пользователя,
 * включая загрузку аватара и смену пароля
 * 
 * @package profile\actions
 * @extends ProfileAction
 */
class Update extends ProfileAction {
    
    /**
     * Метод выполнения обновления профиля
     * Проверяет аутентификацию, метод запроса, CSRF-токен,
     * подготавливает и валидирует данные, выполняет обновление
     * и перенаправляет с соответствующим сообщением
     * 
     * @return void
     */
    public function execute() {
        // Проверка авторизации пользователя
        $this->checkAuthentication();
        
        // Проверка метода запроса (только POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Неправильный метод запроса', '/profile/edit');
            return;
        }
        
        // Проверка CSRF-токена для защиты от межсайтовой подделки запросов
        if (!$this->validateCsrfToken()) {
            $this->redirectWithError('Неверный CSRF-токен', '/profile/edit');
            return;
        }
        
        // Получение текущих данных пользователя
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        // Подготовка данных для обновления
        $updateData = $this->prepareUpdateData();
        
        // Выполнение обновления
        if ($this->processUpdate($user, $updateData)) {
            $this->redirectWithSuccess('/profile/'.$user['username']);
        }
    }
    
    /**
     * Подготавливает данные из POST-запроса для обновления
     * Валидирует email, website, обрабатывает загрузку аватара
     * 
     * @return array Массив данных для обновления (пустые поля отфильтрованы)
     */
    private function prepareUpdateData() {
        $data = [
            'display_name' => trim($_POST['display_name'] ?? ''),
            'email' => $this->validateEmail($_POST['email'] ?? ''),
            'website' => $this->validateWebsite($_POST['website'] ?? ''),
            'bio' => trim($_POST['bio'] ?? ''),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Обработка загрузки нового аватара
        if (!empty($_FILES['avatar']['tmp_name'])) {
            $avatarResult = $this->handleAvatarUpload();
            if ($avatarResult) {
                $data['avatar'] = $avatarResult;
            }
        }

        // Удаление пустых значений из массива
        return array_filter($data);
    }
    
    /**
     * Выполняет обновление данных пользователя
     * Обрабатывает смену пароля и обновление основных данных
     * 
     * @param array $user Текущие данные пользователя
     * @param array $updateData Данные для обновления
     * @return bool true при успешном обновлении
     */
    private function processUpdate($user, $updateData) {
        // Обновление пароля (если указан новый пароль)
        if (!empty($_POST['new_password'])) {
            if (!$this->userModel->updatePassword(
                $user['id'],
                $_POST['current_password'] ?? '',
                $_POST['new_password']
            )) {
                $this->redirectWithError('Неверный текущий пароль', '/profile/edit');
                return false;
            }
        }

        // Обновление остальных данных пользователя
        if (!$this->userModel->update($user['id'], $updateData)) {
            $this->redirectWithError('Ошибка при сохранении данных', '/profile/edit');
            return false;
        }

        // Обновление данных в сессии (имя и аватар)
        $this->updateSession($updateData);
        return true;
    }
    
    /**
     * Обрабатывает загрузку нового аватара пользователя
     * Проверяет тип файла, размер, сохраняет в директорию avatars
     * 
     * @return string|null Имя загруженного файла или null при ошибке
     */
    private function handleAvatarUpload() {
        $file = $_FILES['avatar'];
        
        // Проверка ошибок загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWithError('Ошибка загрузки файла', '/profile/edit');
            return null;
        }

        // Создание директории для аватаров, если не существует
        $uploadDir = UPLOADS_PATH . '/avatars/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Разрешенные типы файлов
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        // Проверка типа файла
        if (!in_array($fileType, $allowedTypes)) {
            $this->redirectWithError('Допустимы только JPG, PNG, GIF или WebP', '/profile/edit');
            return null;
        }

        // Проверка размера файла (макс. 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->redirectWithError('Максимальный размер файла - 5MB', '/profile/edit');
            return null;
        }

        // Генерация уникального имени файла
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        // Сохранение файла
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->redirectWithError('Ошибка загрузки аватара', '/profile/edit');
            return null;
        }

        return $filename;
    }
    
    /**
     * Обновляет данные пользователя в сессии
     * 
     * @param array $data Обновленные данные
     * @return void
     */
    private function updateSession($data) {
        if (isset($data['display_name'])) {
            $_SESSION['display_name'] = $data['display_name'];
        }
        if (isset($data['avatar'])) {
            $_SESSION['avatar'] = $data['avatar'];
        }
    }
    
    /**
     * Валидирует email-адрес
     * Проверяет формат и уникальность (не занят другим пользователем)
     * 
     * @param string $email Email для проверки
     * @return string Валидный email
     * @throws \Exception При ошибке валидации (через redirectWithError)
     */
    private function validateEmail($email) {
        // Проверка формата email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('Некорректный email', '/profile/edit');
            return '';
        }
        
        // Проверка уникальности email (не занят другим пользователем)
        $existingUser = $this->userModel->getByEmail($email);
        if ($existingUser && $existingUser['id'] != $_SESSION['user_id']) {
            $this->redirectWithError('Этот email уже используется другим пользователем', '/profile/edit');
            return '';
        }
        
        return $email;
    }
    
    /**
     * Валидирует URL веб-сайта
     * 
     * @param string $website URL для проверки
     * @return string|null Валидный URL или null
     * @throws \Exception При ошибке валидации (через redirectWithError)
     */
    private function validateWebsite($website) {
        if ($website && !filter_var($website, FILTER_VALIDATE_URL)) {
            $this->redirectWithError('Некорректный URL сайта', '/profile/edit');
            return null;
        }
        return $website ?: null;
    }
}