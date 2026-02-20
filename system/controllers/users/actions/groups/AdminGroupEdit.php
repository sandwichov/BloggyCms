<?php

namespace users\actions\groups;

/**
 * Действие редактирования группы пользователей в административной панели
 * Отображает форму редактирования существующей группы и обрабатывает её отправку,
 * включая обновление названия, описания и флага группы по умолчанию
 * 
 * @package users\actions\groups
 * @extends AdminGroupAction
 */
class AdminGroupEdit extends AdminGroupAction {
    
    /**
     * Метод выполнения редактирования группы
     * Проверяет права доступа, ID, существование группы,
     * обрабатывает POST-запрос для сохранения или отображает форму с текущими данными
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

            // Получение ID группы из параметров
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID группы не указан');
            }

            // Загрузка данных группы
            $group = $this->userModel->getGroupById($id);
            if (!$group) {
                throw new \Exception('Группа не найдена');
            }

            // Обработка POST-запроса (сохранение изменений)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id);
                return;
            }

            // Отображение формы редактирования
            $this->renderEditForm($group);

        } catch (\Exception $e) {
            // Обработка ошибок
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/user-groups');
        }
    }
    
    /**
     * Обрабатывает POST-запрос на обновление группы
     * 
     * @param int $id ID группы
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest($id) {
        // Валидация обязательного поля
        if (empty($_POST['name'])) {
            throw new \Exception('Название группы обязательно');
        }

        // Подготовка данных для обновления
        $groupData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        // Обновление группы через модель пользователей
        $this->userModel->updateGroup($id, $groupData);

        // Уведомление об успехе и перенаправление
        \Notification::success('Группа успешно обновлена');
        $this->redirect(ADMIN_URL . '/user-groups');
    }
    
    /**
     * Отображает форму редактирования группы
     * 
     * @param array $group Данные группы
     * @return void
     */
    private function renderEditForm($group) {
        $this->render('admin/user-groups/edit', [
            'group' => $group,
            'pageTitle' => 'Редактирование группы'
        ]);
    }
}