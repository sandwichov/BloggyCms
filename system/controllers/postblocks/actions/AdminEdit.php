<?php

namespace postblocks\actions;

/**
 * Действие редактирования настроек постблока в административной панели
 * Отображает форму редактирования для конкретного постблока
 * и обрабатывает сохранение его настроек
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminEdit extends PostBlockAction {
    
    /**
     * Метод выполнения редактирования постблока
     * Проверяет права доступа, загружает данные блока,
     * обрабатывает POST-запрос для сохранения настроек или отображает форму
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }

        // Получение системного имени блока из GET-параметров
        $systemName = $_GET['system_name'] ?? '';
        
        // Проверка наличия системного имени
        if (empty($systemName)) {
            \Notification::error('Системное имя блока не указано');
            $this->redirect(ADMIN_URL . '/post-blocks');
            return;
        }

        // Получение данных постблока через менеджер
        $postBlock = $this->postBlockManager->getPostBlock($systemName);
        if (!$postBlock) {
            \Notification::error('Блок не найден');
            $this->redirect(ADMIN_URL . '/post-blocks');
            return;
        }

        // Получение текущих настроек блока из базы данных
        $settings = $this->postBlockModel->getBlockSettings($systemName);

        // Обработка POST-запроса (сохранение настроек)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSettingsUpdate($systemName, $postBlock, $settings);
            return;
        }

        // Отображение формы редактирования
        $this->render('admin/post_blocks/edit', [
            'postBlock' => $postBlock,           // Данные блока
            'settings' => $settings,              // Текущие настройки
            'shortcodes' => $postBlock['class']->getShortcodes(), // Шорткоды блока
            'pageTitle' => 'Редактирование блока: ' . $postBlock['name'] // Заголовок
        ]);
    }

    /**
     * Обрабатывает обновление настроек блока из POST-запроса
     * 
     * @param string $systemName Системное имя блока
     * @param array $postBlock Данные постблока
     * @param array $currentSettings Текущие настройки (не используются)
     * @return void
     */
    private function handleSettingsUpdate($systemName, $postBlock, $currentSettings) {
        // Получение значений из POST-запроса
        $enableInPosts = isset($_POST['enable_in_posts']) ? 1 : 0;
        $enableInPages = isset($_POST['enable_in_pages']) ? 1 : 0;
        $template = $_POST['template'] ?? '';

        // Сохранение настроек через модель
        $success = $this->postBlockModel->updateBlockSettings($systemName, [
            'enable_in_posts' => $enableInPosts,
            'enable_in_pages' => $enableInPages,
            'template' => $template
        ]);

        // Уведомление о результате
        if ($success) {
            \Notification::success('Настройки блока обновлены');
        } else {
            \Notification::error('Ошибка при сохранении настроек');
        }

        // Перенаправление обратно на страницу редактирования
        $this->redirect(ADMIN_URL . '/post-blocks/edit?system_name=' . $systemName);
    }
}