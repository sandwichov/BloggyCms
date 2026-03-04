<?php

class DefaultHeaderBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return "Header";
    }

    public function getSystemName(): string {
        return "DefaultHeaderBlock";
    }

    public function getDescription(): string {
        return "Шапка сайта с логотипом, меню, поиском и профилем";
    }

    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }

    public function getVersion(): string {
        return '1.0.0';
    }

    public function getTemplate(): string {
        return 'default';
    }

    public function getSettingsForm($currentSettings = []): string {
        $allMenus = MenuRenderer::getAllMenusForSelect();
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets = [
            new \Fieldset('Логотип и брендинг', [
                'icon' => 'bi bi-brush',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::blockImage('logo_path', [
                        'title' => 'Логотип',
                        'hint' => 'Загрузите логотип сайта',
                        'default' => $settings['logo_path'] ?? '',
                        'upload_path' => 'uploads/images/html_blocks/' . $this->getSystemName() . '/',
                        'preview_size' => '80px'
                    ]),
                    \FieldFactory::string('logo_alt', [
                        'title' => 'Alt текст логотипа',
                        'default' => $settings['logo_alt'] ?? 'Логотип сайта',
                        'column' => '6',
                        'placeholder' => 'Описание логотипа'
                    ]),
                    \FieldFactory::string('site_name', [
                        'title' => 'Название сайта',
                        'default' => $settings['site_name'] ?? SettingsHelper::get('general', 'site_name', 'BloggyCMS'),
                        'placeholder' => 'Введите название сайта',
                        'column' => '6',
                        'hint' => 'Отображается рядом с логотипом'
                    ]),
                    \FieldFactory::string('logo_link', [
                        'title' => 'Ссылка логотипа',
                        'default' => $settings['logo_link'] ?? '/',
                        'placeholder' => '/',
                        'column' => '6',
                        'hint' => 'Куда ведет клик по логотипу'
                    ])
                ]
            ]),
            
            new \Fieldset('Навигация', [
                'icon' => 'bi bi-menu-button-wide',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::select('main_menu_id', [
                        'title' => 'Главное меню',
                        'options' => ['' => '-- Выберите меню --'] + $allMenus,
                        'required' => true,
                        'hint' => 'Основное меню навигации'
                    ]),
                    \FieldFactory::select('profile_menu_id', [
                        'title' => 'Меню профиля',
                        'options' => ['' => '-- Не показывать --'] + $allMenus,
                        'hint' => 'Выпадающее меню для авторизованного пользователя'
                    ])
                ]
            ]),
            
            new \Fieldset('Поиск', [
                'icon' => 'bi bi-search',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('show_search', [
                        'title' => 'Показывать поиск',
                        'default' => $settings['show_search'] ?? 1,
                        'switch' => true,
                        'hint' => 'Отображать поле поиска в шапке'
                    ]),
                    \FieldFactory::string('search_placeholder', [
                        'title' => 'Плейсхолдер поиска',
                        'default' => $settings['search_placeholder'] ?? 'Поиск...',
                        'placeholder' => 'Например: Найти пост...',
                        'column' => '6',
                        'show' => 'field:show_search'
                    ]),
                    \FieldFactory::select('search_page', [
                        'title' => 'Страница поиска',
                        'options' => [
                            '/search' => 'Стандартная страница поиска',
                            '/search/posts' => 'Поиск по постам',
                            '/search/users' => 'Поиск по пользователям'
                        ],
                        'default' => $settings['search_page'] ?? '/search',
                        'hint' => 'Куда отправлять поисковый запрос',
                        'column' => '6',
                        'show' => 'field:show_search'
                    ])
                ]
            ]),
            
            new \Fieldset('Настройки внешнего вида', [
                'icon' => 'bi bi-palette',
                'columns' => 'custom',
                'fields' => [
                    \FieldFactory::checkbox('sticky_header', [
                        'title' => 'Закрепленная шапка',
                        'default' => $settings['sticky_header'] ?? 1,
                        'switch' => true,
                        'column' => '6',
                        'hint' => 'Шапка остается вверху при прокрутке'
                    ]),
                    \FieldFactory::checkbox('show_shadow', [
                        'title' => 'Показывать тень',
                        'default' => $settings['show_shadow'] ?? 1,
                        'switch' => true,
                        'column' => '6',
                        'hint' => 'Добавить легкую тень под шапкой'
                    ]),
                    \FieldFactory::select('container_type', [
                        'title' => 'Тип контейнера',
                        'options' => [
                            'container' => 'Фиксированный (960px)',
                            'container-fluid' => 'На всю ширину'
                        ],
                        'column' => '6',
                        'default' => $settings['container_type'] ?? 'container'
                    ]),
                    \FieldFactory::select('header_height', [
                        'title' => 'Высота шапки',
                        'options' => [
                            'py-2' => 'Компактная',
                            'py-3' => 'Средняя',
                            'py-4' => 'Высокая'
                        ],
                        'column' => '6',
                        'default' => $settings['header_height'] ?? 'py-3'
                    ])
                ]
            ])
        ];
        
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset) { ?>
            <div class="col-md-12"><?= $fieldset->render($settings) ?></div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validateSettings($settings): array {
        if (!is_array($settings)) {
            return [false, ['Настройки должны быть массивом']];
        }
        
        $errors = [];
        if (empty($settings['main_menu_id'])) {
            $errors[] = 'Необходимо выбрать главное меню';
        }
        
        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }
        
        $uploadResult = BlockImageHelper::handleUpload('logo_path', $this->getSystemName(), $settings['logo_path'] ?? '');
        if ($uploadResult['success']) {
            $settings['logo_path'] = $uploadResult['value'];
        }
        $settings['logo_path'] = BlockImageHelper::handleDelete('logo_path', $settings['logo_path'] ?? '');
        unset($settings['logo_path_file'], $settings['remove_logo_path']);
        $settings['logo_alt'] = trim($settings['logo_alt'] ?? 'Логотип сайта');
        $settings['site_name'] = trim($settings['site_name'] ?? SettingsHelper::get('general', 'site_name', 'BloggyCMS'));
        $settings['logo_link'] = trim($settings['logo_link'] ?? '/');
        $settings['main_menu_id'] = $settings['main_menu_id'] ?? '';
        $settings['profile_menu_id'] = $settings['profile_menu_id'] ?? '';
        $settings['show_search'] = isset($settings['show_search']) ? (int)$settings['show_search'] : 1;
        $settings['search_placeholder'] = trim($settings['search_placeholder'] ?? 'Поиск...');
        $settings['search_page'] = $settings['search_page'] ?? '/search';
        $settings['sticky_header'] = isset($settings['sticky_header']) ? (int)$settings['sticky_header'] : 1;
        $settings['show_shadow'] = isset($settings['show_shadow']) ? (int)$settings['show_shadow'] : 1;
        $settings['container_type'] = $settings['container_type'] ?? 'container';
        $settings['header_height'] = $settings['header_height'] ?? 'py-3';
        
        return $settings;
    }
}