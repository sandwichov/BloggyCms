<?php

namespace tags\actions;

/**
 * Действие отображения списка всех тегов в публичной части
 * Показывает страницу со всеми тегами с пагинацией, фильтрацией по количеству постов
 * и настройками из системы (порядок сортировки, количество на странице)
 * 
 * @package tags\actions
 * @extends TagAction
 */
class Index extends TagAction {
    
    /**
     * Метод выполнения отображения списка тегов
     * Получает настройки из SettingsHelper, загружает теги с пагинацией
     * и отображает страницу
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение номера страницы из GET-параметров
            $page = (int)($_GET['page'] ?? 1);
            
            // Получение настроек из SettingsHelper
            $tagsOrder = \SettingsHelper::get('controller_tags', 'tags_order', 'name');
            $minPostsToShow = \SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
            $tagsPerPage = \SettingsHelper::get('controller_tags', 'cont_tags_in_front', 12);
            
            // Защита от отрицательного значения
            if ($tagsPerPage < 1) {
                $tagsPerPage = 12;
            }
            
            // Получение тегов с фильтрацией по количеству постов и пагинацией
            $result = $this->tagModel->getFilteredTags($minPostsToShow, $page, $tagsPerPage, $tagsOrder);
            
            // Отображение страницы с тегами
            $this->render('front/tags/tags', [
                'tags' => $result['tags'],           // Массив тегов для текущей страницы
                'pagination' => $result['pagination'], // Данные для пагинации
                'title' => 'Все теги',                 // Заголовок страницы
                'settings' => [                         // Текущие настройки для отображения
                    'tags_per_page' => $tagsPerPage,
                    'min_posts' => $minPostsToShow,
                    'order' => $tagsOrder
                ]
            ]);
            
        } catch (\Exception $e) {
            // Вывод ошибки напрямую (для отладки)
            echo "Error: " . $e->getMessage();
        }
    }
}