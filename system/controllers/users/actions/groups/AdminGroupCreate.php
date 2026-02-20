<?php

namespace users\actions\groups;

/**
 * Действие создания новой группы пользователей в административной панели
 * Отображает форму создания группы и обрабатывает её отправку,
 * включая установку группы по умолчанию
 * 
 * @package users\actions\groups
 * @extends AdminGroupAction
 */
class AdminGroupCreate extends AdminGroupAction {
    
    /**
     * Метод выполнения создания группы
     * При GET-запросе отображает форму, при POST-запросе обрабатывает сохранение
     * 
     * @return void
     */
    public function execute() {
        try {
            // Проверка прав доступа администратора
            if (!$this->checkAdminAccess()) {
                \Notification::error('У вас нет прав доступа');
                $this->redirect(ADMIN_URL);
                return;
            }

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
     * Обрабатывает POST-запрос на создание группы
     * 
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest() {
        // Валидация обязательного поля
        if (empty($_POST['name'])) {
            throw new \Exception('Название группы обязательно');
        }

        // Подготовка данных для создания группы
        $groupData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        // Создание группы через модель пользователей
        $groupId = $this->userModel->createGroup($groupData);

        // Уведомление об успехе и перенаправление
        \Notification::success('Группа успешно создана');
        $this->redirect(ADMIN_URL . '/user-groups');
    }
    
    /**
     * Отображает форму создания группы
     * 
     * @return void
     */
    private function renderCreateForm() {
        $this->render('admin/user-groups/create', [
            'pageTitle' => 'Создание группы'
        ]);
    }
    
    /**
     * Обрабатывает ошибку при создании группы
     * 
     * @param \Exception $e Исключение
     * @return void
     */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        
        // Повторное отображение формы с введенными данными
        $this->render('admin/user-groups/create', [
            'group' => $_POST,
            'pageTitle' => 'Создание группы'
        ]);
    }
}