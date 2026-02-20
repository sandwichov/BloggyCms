<?php

/**
 * Поле типа "изображение" для системы полей
 * Позволяет загружать, просматривать и удалять изображения
 * Поддерживает два режима работы: обычный и административный
 * 
 * @package Fields
 * @extends Field
 */
class FieldImage extends Field {
    
    /**
     * Рендерит HTML-код поля для загрузки изображения
     * Отображает текущее изображение (если есть), чекбокс для удаления
     * и поле для загрузки нового файла
     * 
     * @param mixed $currentValue Текущее значение поля (имя файла)
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        $uploadPath = $this->options['upload_path'] ?? 'uploads/';
        
        // Формирование URL для превью
        $previewUrl = $value ? BASE_URL . '/' . $uploadPath . $value : '';
        
        // Определение режима работы (админский или обычный)
        $isAdminMode = $this->options['admin_mode'] ?? false;
        
        // ВАЖНО: Для файлов всегда используем простое имя, без settings[]
        $fileFieldName = $this->name . '_file'; // Отдельное поле для файла
        $hiddenFieldName = $isAdminMode ? $this->name : "settings[{$this->name}]";
        $removeFieldName = $isAdminMode ? "remove_{$this->name}" : "remove_{$this->name}";
        
        ob_start();
        ?>
        <div class="image-field">
            <?php if ($previewUrl): ?>
            <div class="mb-3">
                <label class="form-label">Текущее изображение</label>
                <div class="border rounded p-3 text-center">
                    <img src="<?= $previewUrl ?>" 
                        alt="Preview" 
                        class="img-fluid rounded" 
                        style="width: 64px;">
                    <div class="mt-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" 
                                id="<?= $removeFieldName ?>" 
                                name="<?= $removeFieldName ?>" value="1">
                            <label class="form-check-label text-danger" for="<?= $removeFieldName ?>">
                                Удалить изображение
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">
                    <?= $previewUrl ? 'Заменить изображение' : 'Загрузить изображение' ?>
                </label>
                <input type="file" 
                    class="form-control" 
                    name="<?= $fileFieldName ?>" 
                    accept="image/*">
                <input type="hidden" 
                    name="<?= $hiddenFieldName ?>" 
                    value="<?= htmlspecialchars($value) ?>">
                <div class="form-text text-muted">
                    <?= $this->options['hint'] ?? 'Разрешенные форматы: JPG, PNG, GIF, WebP. Максимальный размер: 5MB' ?>
                </div>
            </div>
        </div>
        <?php
        return $this->renderFieldGroup(ob_get_clean());
    }
}