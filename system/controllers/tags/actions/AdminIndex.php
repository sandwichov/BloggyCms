<?php

namespace tags\actions;

/**
 * Действие отображения списка всех тегов в административной панели
 * Главная страница управления тегами, показывает все теги
 * и случайную подсказку для администратора
 * 
 * @package tags\actions
 * @extends TagAction
 */
class AdminIndex extends TagAction {
    
    /**
     * Метод выполнения отображения списка тегов
     * Получает все теги из базы данных, выбирает случайную подсказку
     * и передает их в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        try {
            $tags = $this->tagModel->getAll();
            $hints = [
                "К каждому тегу вы можете добавить свое изображение",
                "Теги имеют дополнительные настройки - для этого перейдите в раздел Настройки и выберите - Компоненты -> теги",
                "Вы можете добавить теги из этого раздела, или создать их динамически при созаднии поста",
                "Вы можете ограничить количество добавляемых тегов к посту в настройках",
                "Вы можете задать дефолтное изображение для тегов, для которых Вы не загрузили собственное - в настройках",
            ];
            
            $randomHint = $hints[array_rand($hints)];

            $settings = [
                'default_image' => \SettingsHelper::get('controller_tags', 'default_image'),
                'tag_prefix' => \SettingsHelper::get('controller_tags', 'tag_prefix', '#'),
                'show_info' => \SettingsHelper::get('controller_tags', 'show_info')
            ];

            $this->render('admin/tags/index', [
                'tags' => $tags,
                'randomHint' => $randomHint,
                'pageTitle' => 'Управление тегами',
                'settings' => $settings
            ]);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке списка тегов');
            $this->redirect(ADMIN_URL);
        }
    }
}