<?php

namespace tags\actions;

/**
 * Действие создания нового тега в административной панели
 * Отображает форму создания тега и обрабатывает её отправку,
 * включая валидацию, проверку уникальности и загрузку изображения
 * 
 * @package tags\actions
 * @extends TagAction
 */
class Create extends TagAction {
    
    /**
     * Метод выполнения создания тега
     * При GET-запросе отображает форму, при POST-запросе обрабатывает сохранение
     * 
     * @return void
     */
    public function execute() {
        try {
            // Обработка POST-запроса (отправка формы)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
                return;
            }
            
            // Отображение формы создания
            $this->renderCreateForm();
            
        } catch (\Exception $e) {
            // Обработка ошибок
            $this->handleError($e);
        }
    }
    
    /**
     * Обрабатывает POST-запрос на создание тега
     * 
     * @return void
     * @throws \Exception При ошибках валидации или загрузки
     */
    private function handlePostRequest() {
        // Получение и очистка названия тега
        $name = trim($_POST['name'] ?? '');
        
        // Валидация: название не может быть пустым
        if (empty($name)) {
            throw new \Exception('Название тега не может быть пустым');
        }
        
        // Проверка уникальности названия
        $existingTags = $this->tagModel->searchByName($name, 1);
        if (!empty($existingTags)) {
            throw new \Exception('Тег с таким названием уже существует');
        }
        
        // Генерация URL-адреса из названия
        $slug = $this->tagModel->createSlugFromName($name);
        
        // Подготовка данных для создания
        $data = [
            'name' => $name,
            'slug' => $slug
        ];
        
        // Обработка загрузки изображения (если есть)
        if (!empty($_FILES['image']['tmp_name'])) {
            $imageName = $this->handleImageUpload();
            $data['image'] = $imageName;
        }
        
        // Создание тега в базе данных
        $this->tagModel->create($data);
        
        // Уведомление об успехе и перенаправление
        \Notification::success('Тег успешно создан');
        $this->redirect(ADMIN_URL . '/tags');
    }
    
    /**
     * Отображает форму создания тега
     * 
     * @return void
     */
    private function renderCreateForm() {
        $this->render('admin/tags/form', [
            'pageTitle' => 'Создание тега'
        ]);
    }
    
    /**
     * Обрабатывает ошибку при создании тега
     * 
     * @param \Exception $e Исключение
     * @return void
     */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        $this->render('admin/tags/form', [
            'pageTitle' => 'Создание тега'
        ]);
    }
    
    /**
     * Обрабатывает загрузку изображения для тега
     * 
     * @return string Имя загруженного файла
     * @throws \Exception При ошибках загрузки
     */
    private function handleImageUpload() {
        $uploadDir = UPLOADS_PATH . '/tags/';
        
        // Создание директории, если не существует
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['image'];
        
        // Валидация типа файла
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new \Exception('Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WebP');
        }
        
        // Валидация размера файла (макс. 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \Exception('Размер файла не должен превышать 2MB');
        }
        
        // Генерация имени файла
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        // Сохранение файла
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('Ошибка при загрузке файла');
        }
        
        return $fileName;
    }
    
    /**
     * Генерирует URL-адрес (slug) из строки для имени файла
     * Транслитерирует кириллицу, удаляет спецсимволы, заменяет пробелы на дефисы
     * 
     * @param string $string Исходная строка
     * @return string Очищенная строка для использования в имени файла
     */
    private function generateSlug($string) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        
        $string = strtr(mb_strtolower($string), $converter);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
}