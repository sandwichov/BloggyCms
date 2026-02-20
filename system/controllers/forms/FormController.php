<?php

/**
 * Контроллер форм для публичного доступа
 */
class FormController extends Controller {
    
    private $formModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->formModel = new FormModel($db);
    }
    
    public function showAction($slug) {
        $action = new \forms\actions\ShowForm($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    public function processAction($slug) {
        $action = new \forms\actions\ProcessForm($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Вспомогательный метод для рендеринга формы
     * (может использоваться в шаблонах через shortcode или прямой вызов)
     */
    public function renderForm($slug) {
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            return '<!-- Форма не найдена или неактивна -->';
        }
        
        // Генерируем CSRF токен
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $structure = $form['structure'] ?? [];
        
        ob_start();
        ?>
        <div class="form-container form-<?= htmlspecialchars($slug) ?>">
            <?php if ($form['description']): ?>
                <div class="form-description mb-4">
                    <?= nl2br(htmlspecialchars($form['description'])) ?>
                </div>
            <?php endif; ?>
            
            <form action="<?= BASE_URL ?>/form/<?= htmlspecialchars($slug) ?>/submit" 
                  method="POST" 
                  class="custom-form"
                  enctype="multipart/form-data"
                  id="form-<?= htmlspecialchars($slug) ?>">
                  
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <?php foreach ($structure as $field): ?>
                    <?php if ($field['type'] === 'submit'): ?>
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">
                                <?= htmlspecialchars($field['label'] ?? 'Отправить') ?>
                            </button>
                        </div>
                    <?php elseif ($field['type'] !== 'hidden'): ?>
                        <div class="form-group mb-3">
                            <label for="field_<?= htmlspecialchars($field['name']) ?>" class="form-label">
                                <?= htmlspecialchars($field['label'] ?? '') ?>
                                <?php if (!empty($field['required'])): ?>
                                    <span class="text-danger">*</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if (in_array($field['type'], ['text', 'email', 'tel', 'number', 'password'])): ?>
                                <input type="<?= $field['type'] ?>" 
                                       id="field_<?= htmlspecialchars($field['name']) ?>" 
                                       name="<?= htmlspecialchars($field['name']) ?>" 
                                       class="form-control"
                                       placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                       value="<?= htmlspecialchars($field['default_value'] ?? '') ?>"
                                       <?= !empty($field['required']) ? 'required' : '' ?>>
                            <?php elseif ($field['type'] === 'textarea'): ?>
                                <textarea id="field_<?= htmlspecialchars($field['name']) ?>" 
                                          name="<?= htmlspecialchars($field['name']) ?>" 
                                          class="form-control"
                                          rows="<?= $field['rows'] ?? 4 ?>"
                                          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
                                          <?= !empty($field['required']) ? 'required' : '' ?>><?= htmlspecialchars($field['default_value'] ?? '') ?></textarea>
                            <?php elseif ($field['type'] === 'select'): ?>
                                <select id="field_<?= htmlspecialchars($field['name']) ?>" 
                                        name="<?= htmlspecialchars($field['name']) ?>" 
                                        class="form-select"
                                        <?= !empty($field['required']) ? 'required' : '' ?>>
                                    <option value=""><?= htmlspecialchars($field['placeholder'] ?? 'Выберите...') ?></option>
                                    <?php foreach ($field['options'] ?? [] as $option): ?>
                                        <option value="<?= htmlspecialchars($option['value'] ?? '') ?>">
                                            <?= htmlspecialchars($option['label'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                            
                            <?php if (!empty($field['description'])): ?>
                                <div class="form-text"><?= htmlspecialchars($field['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <input type="hidden" 
                               name="<?= htmlspecialchars($field['name']) ?>" 
                               value="<?= htmlspecialchars($field['default_value'] ?? '') ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}