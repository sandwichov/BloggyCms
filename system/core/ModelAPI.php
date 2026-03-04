<?php
interface ModelAPI {
    /**
     * Возвращает информацию о модели
     * @return array ['name' => 'posts', 'class' => 'PostModel']
     */
    public static function getModelInfo();
    
    /**
     * Возвращает список методов, доступных через API
     */
    public function getAPIMethods();
    
    /**
     * Вызывает метод API
     */
    public function callAPI($method, $args);
}