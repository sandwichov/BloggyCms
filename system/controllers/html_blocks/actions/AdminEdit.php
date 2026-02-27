<?php

namespace html_blocks\actions;

/**
 * Действие редактирования HTML-блока в админ-панели
 * Обрабатывает форму редактирования существующего HTML-блока с проверкой активности типа блока
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminEdit extends HtmlBlockAction {
    
    /**
     * Метод выполнения редактирования HTML-блока
     * Загружает блок, проверяет активность его типа и обрабатывает обновление данных
     * 
     * @return void
     */
    public function execute() {
        // Проверка административных прав доступа
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            // Получение данных блока по ID
            $block = $this->htmlBlockModel->getById($this->id);
            
            // Проверка существования блока
            if (!$block) {
                \Notification::error('HTML-блок не найден');
                $this->redirect(ADMIN_URL . '/html-blocks');
                return;
            }

            // Определение типа блока
            $blockTypeName = $block['block_type'] ?? 'DefaultBlock';

            // Проверка активности типа блока (только для не-DefaultBlock)
            if ($blockTypeName !== 'DefaultBlock' && !$this->blockTypeManager->isBlockTypeActive($blockTypeName)) {
                \Notification::error('Невозможно редактировать блок: тип блока отключен. Сначала активируйте тип блока.');
                $this->redirect(ADMIN_URL . '/html-blocks');
                return;
            }

            // Загрузка ресурсов для специфичных типов блоков
            if ($blockTypeName !== 'DefaultBlock') {
                $this->blockTypeManager->loadBlockAssets($blockTypeName);
            }

            // Обработка POST-запроса (отправка формы редактирования)
            if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_FILES)) {
                try {
                    // Валидация обязательных полей формы
                    if (empty($_POST['name']) || empty($_POST['slug'])) {
                        \Notification::error('Название и идентификатор блока обязательны для заполнения');
                        $this->renderFormWithData($_POST, $blockTypeName, $block);
                        return;
                    }

                    $typeId = null;
                    $settings = [];
                    
                    // Обработка настроек для специфичных типов блоков
                    if ($blockTypeName !== 'DefaultBlock') {
                        $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
                        if ($blockType) {
                            $typeId = $blockType['id'];
                            $blockInstance = $blockType['class'];
                            $settings = $_POST['settings'] ?? [];
                            
                            // Валидация настроек типа блока
                            list($isValid, $errors) = $blockInstance->validateSettings($settings);
                            if (!$isValid) {
                                \Notification::error('Ошибки в настройках: ' . implode(', ', $errors));
                                $this->renderFormWithData($_POST, $blockTypeName, $block);
                                return;
                            }
                            
                            // Подготовка настроек к сохранению
                            $settings = $blockInstance->prepareSettings($settings);
                        }
                    } else {
                        // Для DefaultBlock - сохраняем HTML из настроек
                        $settings = [
                            'html' => $_POST['settings']['html'] ?? ''
                        ];
                    }

                    // Обработка CSS и JavaScript файлов блока
                    $cssFiles = $this->processAssetFiles($_POST['css_files'] ?? []);
                    $jsFiles = $this->processAssetFiles($_POST['js_files'] ?? []);
                    
                    // Добавление системных ресурсов типа блока (если есть)
                    if ($blockTypeName !== 'DefaultBlock' && isset($blockInstance)) {
                        $systemCss = $blockInstance->getSystemCss();
                        $systemJs = $blockInstance->getSystemJs();
                        
                        $cssFiles = array_merge($systemCss, $cssFiles);
                        $jsFiles = array_merge($systemJs, $jsFiles);
                    }

                    // Подготовка данных для обновления
                    $data = [
                        'name' => $_POST['name'],
                        'slug' => $_POST['slug'],
                        'content' => '',
                        'type_id' => $typeId,
                        'settings' => $settings,
                        'css_files' => $cssFiles,
                        'js_files' => $jsFiles,
                        'inline_css' => $_POST['inline_css'] ?? '',
                        'inline_js' => $_POST['inline_js'] ?? ''
                    ];
                    
                    // Обновление блока в базе данных
                    $result = $this->htmlBlockModel->update($this->id, $data);
                    
                    // Уведомление об успешном обновлении
                    \Notification::success('HTML-блок успешно обновлен');
                    
                    // Перенаправление на список блоков
                    $this->redirect(ADMIN_URL . '/html-blocks');
                    
                } catch (\Exception $e) {
                    // Обработка ошибок обновления
                    \Notification::error('Ошибка при обновлении HTML-блока: ' . $e->getMessage());
                    $this->renderFormWithData($_POST, $blockTypeName, $block);
                }
            } 
            // Обработка GET-запроса (отображение формы редактирования)
            else {
                $this->renderForm($block, $blockTypeName);
            }
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке блока
            \Notification::error('Ошибка при загрузке HTML-блока');
            $this->redirect(ADMIN_URL . '/html-blocks');
        }
    }
}