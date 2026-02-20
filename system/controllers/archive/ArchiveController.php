<?php

/**
* Контроллер архива записей блога
* Отвечает за отображение архивной страницы со всеми постами
*/
class ArchiveController extends Controller {
    /**
    * @var PostModel Модель для работы с записями блога
    */
    private $postModel;

    /**
    * @var array Метаинформация о контроллере
    * Содержит название, автора, версию и описание функциональности
    */
    protected $controllerInfo = [
        'name' => 'Архив записей блога',
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => 'Отображает архив всех постов на сайте'
    ];
    
    /**
    * Конструктор контроллера архива
    * Инициализирует модель работы с постами для получения данных
    *
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->postModel = new PostModel($db);
    }
    
    /**
    * Главное действие контроллера - отображение архива постов
    * Делегирует выполнение специализированному классу действия (action)
    * для обеспечения модульности и разделения ответственности
    *
    * @return mixed Результат выполнения действия (обычно HTML-контент)
    */
    public function indexAction() {
        $action = new \archive\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
}