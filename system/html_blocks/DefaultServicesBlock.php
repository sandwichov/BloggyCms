<?php

class DefaultServicesBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return "Services Character";
    }

    public function getSystemName(): string {
        return "DefaultServicesBlock";
    }

    public function getDescription(): string {
        return "Блок услуг в харизматичном стиле с картинками и описаниями";
    }

    public function getVersion(): string {
        return '1.1.0';
    }

    public function getTemplate(): string {
        return 'default';
    }

    public function getSettingsForm($currentSettings = []): string {
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets[] = new \Fieldset('Заголовочная часть', [
            'icon' => 'bi bi-pencil',
            'columns' => 'custom', // Включаем кастомный режим
            'fields' => [
                \FieldFactory::string('badge', [
                    'title' => 'Бейдж',
                    'column' => '12', // На всю ширину
                    'default' => $settings['badge'] ?? 'Что я делаю',
                    'placeholder' => 'Например: Услуги',
                ]),
                \FieldFactory::string('title', [
                    'title' => 'Заголовок',
                    'column' => '6', // Половина ширины
                    'default' => $settings['title'] ?? 'Разрабатываю <span class="highlight">сложные проекты</span>',
                    'placeholder' => 'Используйте <span class="highlight"> для выделения',
                ]),
                \FieldFactory::textarea('description', [
                    'title' => 'Описание',
                    'column' => '6', // Половина ширины
                    'default' => $settings['description'] ?? 'Помогаю бизнесу расти с помощью качественного кода. От рефакторинга легаси до архитектуры высоконагруженных проектов.',
                    'rows' => 3,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Услуги', [
            'icon' => 'bi bi-grid-3x3',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('columns', [
                    'title' => 'Количество колонок',
                    'column' => '12',
                    'options' => [
                        '3' => '3 колонки',
                        '2' => '2 колонки',
                        '4' => '4 колонки',
                    ],
                    'default' => '3',
                ]),
                \FieldFactory::repeater('services', [
                'title' => 'Список услуг',
                'column' => '12',
                'repeater_columns' => 2,
                'hint' => 'Картинка загружается отдельно для каждой услуги',
                'min_items' => 1,
                'max_items' => 8,
                'fields' => [
                    [
                        'name' => 'image',
                        'title' => 'Изображение',
                        'type' => 'blockimage',
                        'field_column' => '12',
                    ],
                    [
                        'name' => 'title',
                        'title' => 'Название услуги',
                        'type' => 'string',
                        'field_column' => '12',
                    ],
                    [
                        'name' => 'description',
                        'title' => 'Описание',
                        'type' => 'textarea',
                        'field_column' => '12',
                    ],
                    [
                        'name' => 'price',
                        'title' => 'Цена (опционально)',
                        'type' => 'string',
                        'field_column' => '12',
                    ],
                ]
            ])
            ]
        ]);

        $fieldsets[] = new \Fieldset('Кнопки', [
            'icon' => 'bi bi-ui-radios',
            'columns' => '12',
            'fields' => [
                \FieldFactory::repeater('buttons', [
                    'title' => 'Кнопки под услугами',
                    'hint' => 'Первая кнопка будет основной, остальные - второстепенные',
                    'column' => '12',
                    'repeater_columns' => 2,
                    'fields' => [
                        [
                            'name' => 'text',
                            'title' => 'Текст кнопки',
                            'type' => 'string',
                            'placeholder' => 'Обсудить проект',
                            'field_column' => '12',
                        ],
                        [
                            'name' => 'url',
                            'title' => 'Ссылка',
                            'type' => 'string',
                            'placeholder' => '/contact',
                            'default' => '#',
                            'field_column' => '12',
                        ],
                    ]
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
                    'default' => 'light',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => 'Цвет фона',
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('text_color', [
                    'title' => 'Цвет текста',
                    'preset' => 'basic',
                    'column' => '6',
                    'show' => 'field:theme = custom',
                ]),
                \FieldFactory::color('accent_color', [
                    'title' => 'Акцентный цвет',
                    'preset' => 'website',
                    'column' => '6',
                    'default' => '#2563eb',
                ]),
                \FieldFactory::color('card_background', [
                    'title' => 'Цвет карточек',
                    'preset' => 'basic',
                    'column' => '6',
                    'default' => $settings['card_background'] ?? '',
                    'hint' => 'Оставьте пустым для автоматического',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Отступы', [
            'icon' => 'bi bi-arrows-expand',
            'columns' => 'custom',
            'fields' => [
                \FieldFactory::select('align', [
                    'title' => 'Выравнивание заголовка',
                    'options' => [
                        'left' => 'Слева',
                        'center' => 'По центру',
                    ],
                    'column' => '12',
                    'default' => 'center',
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => 'Отступ сверху (px)',
                    'default' => 80,
                    'max' => 200,
                    'column' => '6'
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => 'Отступ снизу (px)',
                    'default' => 80,
                    'max' => 200,
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

        if (isset($settings['services']) && is_array($settings['services'])) {
            $repeaterName = 'services';
            $currentItems = $settings['services'];
            
            $updates = BlockImageHelper::handleRepeaterUploads(
                $repeaterName,
                $this->getSystemName(),
                $currentItems
            );
            
            $settings['services'] = BlockImageHelper::applyRepeaterUpdates($currentItems, $updates);

            foreach ($settings['services'] as &$item) {
                $textFields = ['title', 'description', 'price'];
                foreach ($textFields as $field) {
                    if (isset($item[$field])) {
                        $item[$field] = trim($item[$field]);
                    }
                }
            }
        }

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