<?php

namespace categories\actions;

/**
 * Действие удаления категории
 * Обрабатывает удаление категорий с различными опциями: удаление постов или их перемещение
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class Delete extends CategoryAction {
    
    /**
     * Метод выполнения удаления категории
     * Управляет процессом удаления с учетом наличия постов в категории
     * 
     * @return void
     */
    public function execute() {
        // Получение ID категории из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID категории
        if (!$id) {
            \Notification::error('ID категории не указан');
            $this->redirect(ADMIN_URL . '/categories');
            return;
        }

        try {
            // Получение данных категории
            $category = $this->categoryModel->getById($id);
            
            // Получение количества постов в категории
            $postsCount = $this->categoryModel->getPostsCount($id);
            
            // Обработка категории с постами
            if ($postsCount > 0) {
                // Проверка выбора способа удаления через POST
                if (isset($_POST['delete_action'])) {
                    $deleteAction = $_POST['delete_action'];
                    
                    // Вариант 1: Перемещение постов в другую категорию
                    if ($deleteAction === 'move_posts' && !empty($_POST['target_category_id'])) {
                        $targetCategoryId = (int)$_POST['target_category_id'];
                        
                        // Перемещение всех постов
                        $this->categoryModel->movePostsToCategory($id, $targetCategoryId);
                        
                        // Удаление изображения категории если оно существует
                        if ($category && !empty($category['image'])) {
                            $imagePath = UPLOADS_PATH . '/images/' . $category['image'];
                            \FileUpload::delete($imagePath);
                        }
                        
                        // Удаление самой категории
                        $this->categoryModel->delete($id);
                        
                        // Формирование сообщения с правильным склонением
                        $postsWord = get_numeric_ending($postsCount, ['пост', 'поста', 'постов']);
                        \Notification::success("Категория удалена. {$postsCount} {$postsWord} перемещены в выбранную категорию.");
                        
                    } 
                    // Вариант 2: Удаление категории вместе со всеми постами
                    elseif ($deleteAction === 'delete_all') {
                        // Каскадное удаление категории и всех ее постов
                        $this->categoryModel->deleteWithPosts($id);
                        
                        // Формирование сообщения с правильным склонением
                        $postsWord = get_numeric_ending($postsCount, ['пост', 'поста', 'постов']);
                        \Notification::success("Категория и {$postsCount} {$postsWord} удалены.");
                        
                    } 
                    // Обработка некорректного выбора
                    else {
                        \Notification::error('Не выбран способ удаления');
                        $this->redirect(ADMIN_URL . '/categories');
                        return;
                    }
                } 
                // Отображение формы выбора способа удаления
                else {
                    $this->showDeleteOptions($id, $category, $postsCount);
                    return;
                }
                
            } 
            // Обработка пустой категории (без постов)
            else {
                // Удаление изображения категории если оно существует
                if ($category && !empty($category['image'])) {
                    $imagePath = UPLOADS_PATH . '/images/' . $category['image'];
                    \FileUpload::delete($imagePath);
                }
                
                // Удаление категории
                $this->categoryModel->delete($id);
                \Notification::success('Категория успешно удалена');
            }
            
        } catch (\Exception $e) {
            // Обработка исключений при удалении
            \Notification::error('Ошибка при удалении категории: ' . $e->getMessage());
        }
        
        // Редирект на страницу управления категориями
        $this->redirect(ADMIN_URL . '/categories');
    }
    
    /**
     * Отображение формы выбора способа удаления категории с постами
     * Показывает интерфейс для выбора: переместить посты или удалить все
     * 
     * @param int $categoryId ID удаляемой категории
     * @param array|null $category Данные удаляемой категории
     * @param int $postsCount Количество постов в категории
     * @return void
     */
    private function showDeleteOptions($categoryId, $category, $postsCount) {
        // Получение всех категорий кроме удаляемой
        $categories = $this->categoryModel->getAll();
        $otherCategories = array_filter($categories, function($cat) use ($categoryId) {
            return $cat['id'] != $categoryId;
        });
        
        // Рендеринг шаблона с опциями удаления
        $this->render('admin/categories/delete_options', [
            'category' => $category,
            'postsCount' => $postsCount,
            'otherCategories' => $otherCategories,
            'pageTitle' => 'Удаление категории'
        ]);
    }
}