<?php

/**
 * Контроллер поиска на фронтенде
 * Обрабатывает поисковые запросы пользователей и отображает результаты
 * 
 * @package Controllers
 * @extends Controller
 */
class SearchController extends Controller {
    
    /** @var SearchModel Модель для работы с поиском */
    private $searchModel;
    
    /**
     * Конструктор контроллера
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->searchModel = new SearchModel($db);
    }
    
    /**
     * Основное действие для поиска
     * Обрабатывает GET-параметр q и отображает результаты
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \search\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
}