<?php

namespace html_blocks\actions;

/**
 * Действие создания нового HTML-блока в админ-панели
 * Обрабатывает создание HTML-блоков различных типов с поддержкой настройки ресурсов и валидации
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminCreate extends HtmlBlockAction {
    
    /**
     * Метод выполнения создания HTML-блока
     * Обрабатывает форму создания блока, включая валидацию настроек и обработку ресурсов
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
        
        // Определение типа блока из параметров запроса (по умолчанию DefaultBlock)
        $blockTypeName = $_GET['type'] ?? 'DefaultBlock';
        
        // Загрузка ресурсов для специфичных типов блоков
        if ($blockTypeName !== 'DefaultBlock') {
            $this->blockTypeManager->loadBlockAssets($blockTypeName);
        }
        
        // Обработка POST-запроса (отправка формы создания)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_FILES)) {
            try {
                // Валидация обязательных полей формы
                if (empty($_POST['name']) || empty($_POST['slug'])) {
                    \Notification::error('Название и идентификатор блока обязательны для заполнения');
                    $this->renderFormWithData($_POST, $blockTypeName);
                    return;
                }

                $blockType = null;
                $settings = [];
                
                // Обработка настроек для специфичных типов блоков
                if ($blockTypeName !== 'DefaultBlock') {
                    $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
                    if ($blockType) {
                        $blockInstance = $blockType['class'];
                        $settings = $_POST['settings'] ?? [];
                        
                        // Валидация настроек типа блока
                        list($isValid, $errors) = $blockInstance->validateSettings($settings);
                        if (!$isValid) {
                            \Notification::error('Ошибки в настройках: ' . implode(', ', $errors));
                            $this->renderFormWithData($_POST, $blockTypeName);
                            return;
                        }
                        
                        // Подготовка настроек к сохранению
                        $settings = $blockInstance->prepareSettings($settings);
                    }
                }

                // Обработка CSS и JavaScript файлов блока
                $cssFiles = $this->processAssetFiles($_POST['css_files'] ?? []);
                $jsFiles = $this->processAssetFiles($_POST['js_files'] ?? []);
                
                // Добавление системных ресурсов типа блока (если есть)
                if ($blockTypeName !== 'DefaultBlock' && $blockType && $blockType['class']) {
                    $systemCss = $blockType['class']->getSystemCss();
                    $systemJs = $blockType['class']->getSystemJs();
                    
                    $cssFiles = array_merge($systemCss, $cssFiles);
                    $jsFiles = array_merge($systemJs, $jsFiles);
                }

                // Подготовка данных для сохранения в базу данных
                $data = [
                    'name' => $_POST['name'],
                    'slug' => $_POST['slug'],
                    'content' => '', // Контент остается пустым (генерируется динамически)
                    'type_id' => $blockType ? $blockType['id'] : null,
                    'settings' => $settings,
                    'css_files' => $cssFiles,
                    'js_files' => $jsFiles,
                    'inline_css' => $_POST['inline_css'] ?? '',
                    'inline_js' => $_POST['inline_js'] ?? '',
                    'template' => $_POST['template'] ?? 'default'
                ];

                // Создание блока в базе данных
                $this->htmlBlockModel->create($data);
                
                // Уведомление об успешном создании
                \Notification::success('HTML-блок успешно создан');
                
                // Перенаправление на список блоков
                $this->redirect(ADMIN_URL . '/html-blocks');
                
            } catch (\Exception $e) {
                // Обработка ошибок создания блока
                \Notification::error('Ошибка при создании HTML-блока: ' . $e->getMessage());
                $this->renderFormWithData($_POST, $blockTypeName);
            }
        } 
        // Обработка GET-запроса (отображение пустой формы)
        else {
            $this->renderForm(null, $blockTypeName);
        }
    }
}