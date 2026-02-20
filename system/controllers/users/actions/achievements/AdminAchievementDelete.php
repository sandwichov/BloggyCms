<?php

namespace users\actions\achievements;

/**
 * Действие удаления достижения (ачивки) в административной панели
 * Удаляет указанную ачивку из базы данных вместе с изображением и связанными данными
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementDelete extends AdminAchievementAction {
    
    /**
     * Метод выполнения удаления ачивки
     * Проверяет права доступа, наличие ID, существование ачивки,
     * удаляет файл изображения (если есть) и удаляет ачивку из БД
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

            // Получение ID ачивки из параметров
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID ачивки не указан');
            }
            
            // Проверка существования ачивки
            $achievement = $this->userModel->getAchievementById($id);
            if (!$achievement) {
                throw new \Exception('Ачивка не найдена');
            }
            
            // Удаление файла изображения, если существует
            if (!empty($achievement['image'])) {
                $uploadDir = UPLOADS_PATH . '/achievements/';
                $imagePath = $uploadDir . $achievement['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            // Удаление ачивки из базы данных
            $this->userModel->deleteAchievement($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Ачивка успешно удалена');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке
            \Notification::error('Ошибка при удалении ачивки: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу со списком ачивок
        $this->redirect(ADMIN_URL . '/user-achievements');
    }
}