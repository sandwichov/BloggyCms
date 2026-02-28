<?php

class DefaultHeroBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return "Hero";
    }

    public function getSystemName(): string {
        return "DefaultHeroBlock";
    }

    public function getDescription(): string {
        return "Привлекающий внимание блок с заголовком, текстом, кнопками и изображением.";
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
        
        $settings = array_merge([], $currentSettings);
        
        $fieldsets[] = new \Fieldset('Основной контент', [
            'icon' => 'bi bi-pencil-square',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('show_title', [
                    'title' => 'Показывать заголовок',
                    'default' => $settings['show_title'] ?? 1,
                    'switch' => true,
                ]),
                \FieldFactory::string('title_text', [
                    'title' => 'Текст заголовка',
                    'default' => $settings['title_text'] ?? 'Заголовок, который привлекает внимание',
                    'placeholder' => 'Например: Создайте свой идеальный сайт',
                    'show' => 'field:show_title',
                ]),
                $color = FieldFactory::color('title_color', [
                    'title' => 'Цвет заголовка',
                    'preset' => 'basic',
                    'show' => 'field:show_title',
                ]),
                \FieldFactory::checkbox('show_subtitle', [
                    'title' => 'Показывать подзаголовок',
                    'default' => $settings['show_subtitle'] ?? 1,
                    'switch' => true,
                ]),
                \FieldFactory::string('subtitle_text', [
                    'title' => 'Текст подзаголовка',
                    'default' => $settings['subtitle_text'] ?? 'Краткое описание преимуществ',
                    'placeholder' => 'Например: Просто, быстро и эффективно',
                    'show' => 'field:show_subtitle',
                ]),
                \FieldFactory::color('subtitle_color', [
                    'title' => 'Цвет подзаголовка',
                    'preset' => 'basic',
                    'show' => 'field:show_subtitle',
                ]),
                \FieldFactory::checkbox('show_description', [
                    'title' => 'Показывать описание',
                    'default' => $settings['show_description'] ?? 1,
                    'switch' => true,
                ]),
                \FieldFactory::textarea('description_text', [
                    'title' => 'Текст описания',
                    'default' => $settings['description_text'] ?? 'Более подробный текст, который раскрывает суть предложения и побуждает пользователя к действию. Расскажите о ключевых выгодах.',
                    'placeholder' => 'Введите описание...',
                    'rows' => 4,
                    'show' => 'field:show_description',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Кнопки', [
            'icon' => 'bi bi-ui-radios',
            'columns' => '12',
            'fields' => [
                \FieldFactory::repeater('buttons', [
                    'title' => 'Кнопки действий',
                    'hint' => 'Добавьте кнопки для призыва к действию.',
                    'fields' => [
                        [
                            'name' => 'text',
                            'title' => 'Текст кнопки',
                            'type' => 'string',
                            'placeholder' => 'Например: Начать',
                            'default' => 'Кнопка',
                        ],
                        [
                            'name' => 'url',
                            'title' => 'Ссылка',
                            'type' => 'string',
                            'placeholder' => '/contact или https://...',
                            'default' => '#',
                        ],
                        [
                            'name' => 'type',
                            'title' => 'Тип кнопки',
                            'type' => 'select',
                            'options' => [
                                'primary' => 'Основная (Primary)',
                                'outline' => 'Контурная (Outline)',
                            ],
                            'default' => 'primary',
                        ],
                        [
                            'name' => 'size',
                            'title' => 'Размер',
                            'type' => 'select',
                            'options' => [
                                'md' => 'Средний',
                                'lg' => 'Большой',
                            ],
                            'default' => 'lg',
                        ],
                        [
                            'name' => 'icon',
                            'title' => 'Иконка (опционально)',
                            'type' => 'icon',
                            'hint' => 'Например: bs:arrow-right',
                        ],
                    ]
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Изображение', [
            'icon' => 'bi bi-image',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('show_image', [
                    'title' => 'Показывать изображение',
                    'default' => $settings['show_image'] ?? 1,
                    'switch' => true,
                ]),
                \FieldFactory::blockImage('image', [
                    'title' => 'Изображение',
                    'hint' => 'Рекомендуемый размер: 600x400px. Форматы: JPG, PNG, WebP.',
                    'upload_path' => 'uploads/images/html_blocks/' . $this->getSystemName() . '/',
                    'preview_size' => '120px',
                    'show' => 'field:show_image',
                ]),
                \FieldFactory::select('image_position', [
                    'title' => 'Позиция изображения',
                    'options' => [
                        'right' => 'Справа от текста',
                        'left' => 'Слева от текста',
                    ],
                    'default' => 'right',
                    'show' => 'field:show_image',
                ]),
                \FieldFactory::select('image_alignment', [
                    'title' => 'Вертикальное выравнивание',
                    'options' => [
                        'center' => 'По центру',
                        'top' => 'По верхнему краю',
                    ],
                    'default' => 'center',
                    'show' => 'field:show_image',
                ]),
                \FieldFactory::number('image_width', [
                    'title' => 'Ширина колонки с изображением (%)',
                    'default' => 50,
                    'min' => 30,
                    'max' => 70,
                    'step' => 5,
                    'hint' => 'Процент ширины для колонки с изображением. Контент займет оставшуюся часть.',
                    'show' => 'field:show_image',
                ]),
                \FieldFactory::checkbox('image_rounded', [
                    'title' => 'Скругленные углы',
                    'default' => 1,
                    'switch' => true,
                    'show' => 'field:show_image',
                ]),
                \FieldFactory::checkbox('image_shadow', [
                    'title' => 'Тень',
                    'default' => 1,
                    'switch' => true,
                    'show' => 'field:show_image',
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Фон', [
            'icon' => 'bi bi-brush',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('background_type', [
                    'title' => 'Тип фона',
                    'options' => [
                        'color' => 'Цвет',
                        'gradient' => 'Градиент',
                        'image' => 'Изображение',
                    ],
                    'default' => 'color',
                ]),
                \FieldFactory::color('background_color', [
                    'title' => 'Цвет фона',
                    'preset' => 'basic',
                    'show' => 'field:background_type = color',
                ]),
                \FieldFactory::color('gradient_start', [
                    'title' => 'Начальный цвет градиента',
                    'preset' => 'basic',
                    'show' => 'field:background_type = gradient',
                ]),
                \FieldFactory::color('gradient_end', [
                    'title' => 'Конечный цвет градиента',
                    'preset' => 'basic',
                    'show' => 'field:background_type = gradient',
                ]),
                \FieldFactory::select('gradient_direction', [
                    'title' => 'Направление градиента',
                    'options' => [
                        'to bottom' => 'Сверху вниз',
                        'to right' => 'Слева направо',
                        'to bottom right' => 'По диагонали',
                    ],
                    'default' => 'to bottom',
                    'show' => 'field:background_type = gradient',
                ]),
                \FieldFactory::blockImage('background_image', [
                    'title' => 'Фоновое изображение',
                    'hint' => 'Рекомендуемый размер: 1920x1080px.',
                    'upload_path' => 'uploads/images/html_blocks/' . $this->getSystemName() . '/backgrounds/',
                    'preview_size' => '80px',
                    'show' => 'field:background_type = image',
                ]),
                \FieldFactory::select('background_overlay', [
                    'title' => 'Затемнение/оверлей',
                    'options' => [
                        'none' => 'Нет',
                        'dark' => 'Темный',
                        'light' => 'Светлый',
                        'primary' => 'Цвет темы',
                    ],
                    'default' => 'none',
                    'show' => 'field:background_type = image',
                ]),
            ]
        ]);
        $fieldsets[] = new \Fieldset('Отступы и выравнивание', [
            'icon' => 'bi bi-arrows-expand',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('content_alignment', [
                    'title' => 'Выравнивание текста',
                    'options' => [
                        'left' => 'Слева',
                        'center' => 'По центру',
                        'right' => 'Справа',
                    ],
                    'default' => 'left',
                ]),
                \FieldFactory::number('padding_top', [
                    'title' => 'Отступ сверху (pt-*)',
                    'hint' => 'Значение от 0 до 5 для Bootstrap pt-* классов. 0 = pt-0, 5 = pt-5.',
                    'default' => 5,
                    'min' => 0,
                    'max' => 5,
                    'step' => 1,
                ]),
                \FieldFactory::number('padding_bottom', [
                    'title' => 'Отступ снизу (pb-*)',
                    'hint' => 'Значение от 0 до 5 для Bootstrap pb-* классов.',
                    'default' => 5,
                    'min' => 0,
                    'max' => 5,
                    'step' => 1,
                ]),
                \FieldFactory::checkbox('full_width', [
                    'title' => 'На всю ширину',
                    'default' => 0,
                    'switch' => true,
                    'hint' => 'Контент будет внутри контейнера, но фон блока будет на всю ширину экрана.',
                ]),
            ]
        ]);
        $fieldsets[] = new \Fieldset('Дополнительно', [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::string('custom_css_class', [
                    'title' => 'Дополнительный CSS класс',
                    'default' => $settings['custom_css_class'] ?? '',
                    'placeholder' => 'my-custom-hero',
                ]),
            ]
        ]);
        
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
        
        if (!empty($settings['show_title']) && empty(trim($settings['title_text'] ?? ''))) {
            $errors[] = 'Текст заголовка не может быть пустым, если заголовок включен.';
        }
        if (!empty($settings['show_subtitle']) && empty(trim($settings['subtitle_text'] ?? ''))) {
            $errors[] = 'Текст подзаголовка не может быть пустым, если подзаголовок включен.';
        }
        
        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }

        $uploadResult = BlockImageHelper::handleUpload('image', $this->getSystemName(), $settings['image'] ?? '');
        if ($uploadResult['success']) {
            $settings['image'] = $uploadResult['value'];
        }
        $settings['image'] = BlockImageHelper::handleDelete('image', $settings['image'] ?? '');
        unset($settings['image_file'], $settings['remove_image']);
        $uploadResultBg = BlockImageHelper::handleUpload('background_image', $this->getSystemName() . '/backgrounds', $settings['background_image'] ?? '');
        if ($uploadResultBg['success']) {
            $settings['background_image'] = $uploadResultBg['value'];
        }
        $settings['background_image'] = BlockImageHelper::handleDelete('background_image', $settings['background_image'] ?? '');
        unset($settings['background_image_file'], $settings['remove_background_image']);
        if (isset($settings['buttons']) && is_array($settings['buttons'])) {
            $filteredButtons = [];
            foreach ($settings['buttons'] as $button) {
                if (!empty(trim($button['text'] ?? '')) && !empty(trim($button['url'] ?? ''))) {
                    $filteredButtons[] = [
                        'text' => trim($button['text']),
                        'url' => trim($button['url']),
                        'type' => $button['type'] ?? 'primary',
                        'size' => $button['size'] ?? 'md',
                        'icon' => trim($button['icon'] ?? ''),
                    ];
                }
            }
            $settings['buttons'] = $filteredButtons;
        } else {
            $settings['buttons'] = [];
        }

        $settings['show_title'] = isset($settings['show_title']) ? (int)$settings['show_title'] : 1;
        $settings['title_text'] = trim($settings['title_text'] ?? 'Заголовок');
        $settings['title_color'] = trim($settings['title_color'] ?? 'var(--tg-text)');
        $settings['show_subtitle'] = isset($settings['show_subtitle']) ? (int)$settings['show_subtitle'] : 1;
        $settings['subtitle_text'] = trim($settings['subtitle_text'] ?? '');
        $settings['subtitle_color'] = trim($settings['subtitle_color'] ?? 'var(--tg-text-secondary)');
        $settings['show_description'] = isset($settings['show_description']) ? (int)$settings['show_description'] : 1;
        $settings['description_text'] = trim($settings['description_text'] ?? '');
        $settings['show_image'] = isset($settings['show_image']) ? (int)$settings['show_image'] : 1;
        $settings['image_position'] = $settings['image_position'] ?? 'right';
        $settings['image_alignment'] = $settings['image_alignment'] ?? 'center';
        $settings['image_width'] = min(70, max(30, (int)($settings['image_width'] ?? 50)));
        $settings['image_rounded'] = isset($settings['image_rounded']) ? (int)$settings['image_rounded'] : 1;
        $settings['image_shadow'] = isset($settings['image_shadow']) ? (int)$settings['image_shadow'] : 1;
        $settings['background_type'] = $settings['background_type'] ?? 'color';
        $settings['background_color'] = trim($settings['background_color'] ?? 'var(--tg-surface)');
        $settings['gradient_start'] = trim($settings['gradient_start'] ?? 'var(--tg-surface)');
        $settings['gradient_end'] = trim($settings['gradient_end'] ?? 'var(--tg-bg)');
        $settings['gradient_direction'] = $settings['gradient_direction'] ?? 'to bottom';
        $settings['background_overlay'] = $settings['background_overlay'] ?? 'none';
        $settings['content_alignment'] = $settings['content_alignment'] ?? 'left';
        $settings['padding_top'] = min(5, max(0, (int)($settings['padding_top'] ?? 5)));
        $settings['padding_bottom'] = min(5, max(0, (int)($settings['padding_bottom'] ?? 5)));
        $settings['full_width'] = isset($settings['full_width']) ? (int)$settings['full_width'] : 0;
        $settings['custom_css_class'] = trim($settings['custom_css_class'] ?? '');

        return $settings;
    }

    public function processFrontend($settings = [], $templateName = null): string {
        $content = parent::processFrontend($settings, $templateName);
        return $content;
    }
}