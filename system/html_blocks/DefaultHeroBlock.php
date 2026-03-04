<?php

class DefaultHeroBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return "Hero";
    }

    public function getSystemName(): string {
        return "DefaultHeroBlock";
    }

    public function getDescription(): string {
        return "Харизматичный hero-блок с акцентом на личность и типографику";
    }

    public function getVersion(): string {
        return '1.0.0';
    }

    public function getTemplate(): string {
        return 'default';
    }

    public function getSettingsForm($currentSettings = []): string {
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets[] = new \Fieldset('Контент', [
            'icon' => 'bi bi-pencil',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => 'Бейдж (маленький текст сверху)',
                    'default' => $settings['badge'] ?? 'Senior PHP-разработчик',
                    'column' => '6',
                    'placeholder' => 'Например: Senior Developer'
                ]),
                \FieldFactory::string('title', [
                    'title' => 'Заголовок',
                    'default' => $settings['title'] ?? 'Пишу чистый PHP-код и <span class="highlight">учу этому</span> других',
                    'column' => '6',
                    'placeholder' => 'Используйте <span class="highlight"> для выделения'
                ]),
                \FieldFactory::textarea('description', [
                    'title' => 'Описание',
                    'default' => $settings['description'] ?? 'Пишу о рефакторинге, архитектуре, PSR-стандартах и о том, как не скатиться в поддержку 10-летнего проекта на CodeIgniter 2. Подписывайся, будет больно, но полезно.',
                    'rows' => 4,
                    'column' => '12'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Кнопки', [
            'icon' => 'bi bi-ui-radios',
            'columns' => '12',
            'fields' => [
                \FieldFactory::repeater('buttons', [
                    'title' => 'Кнопки',
                    'column' => '12',
                    'repeater_columns' => 2,
                    'hint' => 'Первая кнопка будет основной (с фоном), остальные - второстепенные',
                    'min_items' => 0,
                    'max_items' => 3,
                    'fields' => [
                        [
                            'name' => 'text',
                            'title' => 'Текст кнопки',
                            'type' => 'string',
                            'placeholder' => 'Читать блог',
                            'default' => 'Кнопка'
                        ],
                        [
                            'name' => 'url',
                            'title' => 'Ссылка',
                            'type' => 'string',
                            'placeholder' => '/posts',
                            'default' => '#'
                        ],
                    ]
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Изображение', [
            'icon' => 'bi bi-image',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::blockImage('image', [
                    'title' => 'Изображение',
                    'hint' => 'Рекомендуемый размер: 600x600px. Квадратное или портрет',
                    'upload_path' => 'uploads/images/html_blocks/hero/',
                    'preview_size' => '100px',
                ]),
                \FieldFactory::select('image_style', [
                    'title' => 'Стиль изображения',
                    'options' => [
                        'circle' => 'Круг',
                        'square' => 'Квадрат',
                        'rounded' => 'Скругленные углы',
                    ],
                    'column' => '6',
                    'default' => 'circle',
                ]),
                \FieldFactory::select('image_position', [
                    'title' => 'Позиция изображения',
                    'options' => [
                        'right' => 'Справа',
                        'left' => 'Слева',
                    ],
                    'column' => '6',
                    'default' => 'right'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Цвета и фон', [
            'icon' => 'bi bi-palette',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('theme', [
                    'title' => 'Тема',
                    'options' => [
                        'light' => 'Светлая',
                        'dark' => 'Темная',
                        'custom' => 'Своя',
                    ],
                    'column' => '12',
                    'default' => 'light'
                ]),
                \FieldFactory::color('background_color', [
                    'title' => 'Цвет фона',
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom'
                ]),
                \FieldFactory::color('text_color', [
                    'title' => 'Цвет текста',
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom'
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => 'Акцентный цвет',
                    'preset' => 'website',
                    'column' => '12',
                    'default' => '#2563eb'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Отступы', [
            'icon' => 'bi bi-arrows-expand',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('align', [
                    'title' => 'Выравнивание текста',
                    'options' => [
                        'left' => 'Слева',
                        'center' => 'По центру',
                        'right' => 'Справа',
                    ],
                    'column' => '6',
                    'default' => 'left'
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => 'Отступ сверху (px)',
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6'
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => 'Отступ снизу (px)',
                    'default' => 80,
                    'min' => 0,
                    'max' => 200,
                    'step' => 10,
                    'column' => '6'
                ]),
            ]
        ]);

        ob_start();
        ?>
        <div class="row g-4">
            <?php foreach ($fieldsets as $fieldset): ?>
            <div class="col-12"><?= $fieldset->render($settings) ?></div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }

        if (isset($settings['remove_image']) && $settings['remove_image'] == 1) {
            $settings['image'] = '';
        } else {
            $uploadResult = BlockImageHelper::handleUpload('image', 'hero', $settings['image'] ?? '');
            if ($uploadResult['success']) {
                $settings['image'] = $uploadResult['value'];
            }
        }
        unset($settings['image_file'], $settings['remove_image']);

        if (isset($settings['buttons']) && is_array($settings['buttons'])) {
            $filteredButtons = [];
            foreach ($settings['buttons'] as $button) {
                if (!empty(trim($button['text'] ?? ''))) {
                    $filteredButtons[] = [
                        'text' => trim($button['text']),
                        'url' => trim($button['url'] ?? '#'),
                    ];
                }
            }
            $settings['buttons'] = $filteredButtons;
        }

        return $settings;
    }
}