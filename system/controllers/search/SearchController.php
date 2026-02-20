<?php

/**
 * Контроллер для обработки поисковых запросов на публичной части сайта
 * Отвечает за отображение страницы поиска и результатов поиска
 * 
 * @package Controllers
 * @extends Controller
 */
class SearchController extends Controller {
    
    /**
     * Отображает страницу поиска с результатами
     * Получает поисковый запрос из GET-параметров, выполняет поиск
     * и отображает результаты с пагинацией
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \search\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
}