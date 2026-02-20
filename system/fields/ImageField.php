<?php

/**
 * Поле типа "изображение" для системы пользовательских полей
 * Позволяет загружать, просматривать и удалять изображения
 * Поддерживает валидацию размеров, типов файлов и работу с FileUpload хелпером
 * 
 * @package Fields
 * @extends BaseField
 */
class ImageField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'image'
     */
    public function getType(): string {
        return 'image';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Изображение'
     */
    public function getName(): string {
        return 'Изображение';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Отображает текущее изображение (если есть), чекбокс для удаления
     * и поле для загрузки нового файла
     * 
     * @param mixed $value Текущее значение поля (имя файла)
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $maxSize = $this->config['max_size'] ?? 2048;
        $allowedTypes = $this->config['allowed_types'] ?? 'jpg,jpeg,png,gif,webp';
        
        $html = "<div class='image-field' data-max-size='{$maxSize}' data-allowed-types='{$allowedTypes}'>";
        
        // Отображение текущего изображения
        if (!empty($value)) {
            $html .= "
                <div class='mb-2'>
                    <img src='" . BASE_URL . "/uploads/images/{$value}' 
                         class='img-thumbnail' 
                         style='max-height: 100px;'
                         alt='Превью'>
                    <div class='mt-1'>
                        <label class='form-check-label small'>
                            <input type='checkbox' name='field_{$this->systemName}_delete' value='1' class='form-check-input'>
                            Удалить изображение
                        </label>
                    </div>
                </div>
            ";
        }
        
        // Поле для загрузки нового файла
        $html .= "
            <input type='file' 
                   name='field_{$this->systemName}' 
                   class='form-control form-control-sm image-upload-input'
                   accept='image/*'
                   {$required}>
            <div class='form-text'>
                Макс. размер: " . ($maxSize / 1024) . "MB, форматы: {$allowedTypes}
            </div>
        ";
        
        // Скрытое поле с текущим значением
        $html .= "<input type='hidden' name='field_{$this->systemName}_current' value='" . htmlspecialchars($value) . "'>";
        
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Показывает изображение в увеличенном размере
     * 
     * @param mixed $value Значение поля (имя файла)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">Изображение не загружено</span>';
        
        return "
            <div class='text-center'>
                <img src='" . BASE_URL . "/uploads/images/{$value}' 
                     class='img-fluid rounded'
                     style='max-height: 200px;'
                     alt='Изображение'>
            </div>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Показывает миниатюру изображения
     * 
     * @param mixed $value Значение поля (имя файла)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">Нет</span>';
        
        return "
            <img src='" . BASE_URL . "/uploads/images/{$value}' 
                 class='rounded'
                 style='width: 30px; height: 30px; object-fit: cover;'
                 alt='✓'>
        ";
    }
    
    /**
     * Валидирует значение поля
     * Проверяет только обязательность, загрузка файла валидируется отдельно
     * 
     * @param mixed $value Значение для проверки
     * @return bool true если значение корректно
     */
    public function validate($value): bool {
        if (isset($this->config['required']) && $this->config['required'] && empty($value)) {
            return false;
        }
        return true;
    }
    
    /**
     * Обрабатывает значение перед сохранением
     * Для изображений возвращает имя файла как есть
     * 
     * @param mixed $value Исходное значение
     * @return string Имя файла
     */
    public function processValue($value) {
        return $value;
    }
    
    /**
     * Указывает, что поле требует обработки файлов
     * 
     * @return bool true
     */
    public function requiresFileUpload(): bool {
        return true;
    }
    
    /**
     * Обрабатывает загрузку файла
     * Загружает новый файл, удаляет старый при необходимости
     * 
     * @param array $fileData Данные из $_FILES
     * @param string|null $currentValue Текущее значение поля
     * @return string|null Имя загруженного файла
     * @throws Exception При ошибке загрузки
     */
    public function processFileUpload($fileData, $currentValue = null) {
        if (empty($fileData['tmp_name'])) {
            return $currentValue;
        }
        
        $uploadDir = UPLOADS_PATH . '/images';
        $allowedTypes = explode(',', $this->config['allowed_types'] ?? 'jpg,jpeg,png,gif,webp');
        $maxSize = $this->config['max_size'] ?? 2048;
        
        try {
            // Загрузка нового файла через FileUpload хелпер
            $fileName = FileUpload::upload($fileData, $uploadDir, $allowedTypes, $maxSize);
            
            // Удаление старого файла если он существует
            if (!empty($currentValue)) {
                $oldFilePath = $uploadDir . '/' . $currentValue;
                FileUpload::delete($oldFilePath);
            }
            
            return $fileName;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Обрабатывает удаление файла
     * 
     * @param string|null $currentValue Текущее значение поля
     * @return null Всегда возвращает null
     */
    public function handleDelete($currentValue) {
        if (!empty($currentValue)) {
            $filePath = UPLOADS_PATH . '/images/' . $currentValue;
            FileUpload::delete($filePath);
        }
        return null;
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать максимальный размер файла и разрешенные форматы
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $maxSize = htmlspecialchars($this->config['max_size'] ?? '2048');
        $allowedTypes = htmlspecialchars($this->config['allowed_types'] ?? 'jpg,jpeg,png,gif,webp');
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Максимальный размер (КБ)</label>
                        <input type='number' class='form-control' name='config[max_size]' value='{$maxSize}' min='100' max='10240'>
                        <div class='form-text'>Максимальный размер файла в килобайтах</div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Разрешенные форматы</label>
                        <input type='text' class='form-control' name='config[allowed_types]' value='{$allowedTypes}' placeholder='jpg,jpeg,png,gif,webp'>
                        <div class='form-text'>Через запятую, без точек</div>
                    </div>
                </div>
            </div>
        ";
    }
}