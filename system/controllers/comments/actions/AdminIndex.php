<?php

namespace comments\actions;

/**
 * Действие отображения списка комментариев в админ-панели
 * Показывает все комментарии с пагинацией для управления модерацией и редактированием
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class AdminIndex extends CommentAction {
    
    /**
     * Метод выполнения отображения списка комментариев
     * Загружает комментарии с пагинацией и отображает интерфейс управления
     * 
     * @return void
     */
    public function execute() {
        try {
            // Определение параметров пагинации
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = 20; // Количество комментариев на одной странице
            
            // Получение комментариев с пагинацией через модель
            $result = $this->commentModel->getAllComments($page, $perPage);
            
            /**
             * Рендеринг шаблона админ-панели комментариев
             * 
             * @param string $template Путь к шаблону (admin/comments/index)
             * @param array $data Данные для шаблона:
             * - comments: массив комментариев с информацией о постах и авторах
             * - total: общее количество комментариев в базе данных
             * - pages: общее количество страниц пагинации
             * - current_page: текущая страница
             * - pageTitle: заголовок страницы
             */
            $this->render('admin/comments/index', [
                'comments' => $result['comments'],
                'total' => $result['total'],
                'pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'pageTitle' => 'Управление комментариями'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке комментариев
            \Notification::error('Ошибка при загрузке списка комментариев');
            $this->redirect(ADMIN_URL);
        }
    }
}