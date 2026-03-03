<?php

/**
* Пространство имен для действий архива
* Содержит абстрактные и конкретные реализации действий для работы с архивом постов
*/
namespace archive\actions;

/**
* Класс действия "Индекс" для архива
* Реализует отображение главной страницы архива с группировкой постов по месяцам
*/
class Index extends ArchiveAction {
    
    /**
    * Выполнение действия по отображению архива
    * Получает архивные данные, группирует посты по годам и месяцам,
    * передает данные в шаблон для отображения
    *
    * Основной алгоритм:
    * 1. Получение структуры архива (годы и месяцы с количеством постов)
    * 2. Для каждого месяца получение списка постов
    * 3. Формирование массива с группировкой постов по месяцам
    * 4. Рендеринг страницы архива
    *
    * @throws \Exception В случае ошибки отображает страницу 500
    */
    public function execute() {
        try {
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Архив постов');
            $this->setPageTitle('Архив постов');
            
            $archiveData = $this->postModel->getArchive();

            $postsByMonth = [];
            
            foreach ($archiveData as $archiveItem) {
                $year = $archiveItem['year'];
                $month = $archiveItem['month'];
                $posts = $this->postModel->getPostsByArchive($year, $month);
                $postsByMonth[$year][$month] = $posts;
            }
            
            $this->render('front/archive/archive', [
                'archiveData' => $archiveData,
                'postsByMonth' => $postsByMonth
            ]);
            
        } catch (\Exception $e) {
            $this->render('front/500', [], 500);
        }
    }
}