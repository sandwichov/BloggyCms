<?php

class DefaultBreadcrumbsBlock extends BaseHtmlBlock {
    
    public function getName(): string {
        return "Хлебные крошки";
    }

    public function getSystemName(): string {
        return "DefaultBreadcrumbsBlock";
    }

    public function getDescription(): string {
        return "Навигационная цепочка (хлебные крошки)";
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
        
        $fieldsets = [];
        
        $fieldsets[] = new \Fieldset('Основные настройки', [
            'icon' => 'bi bi-gear',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('show_home', [
                    'title' => 'Показывать главную',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('show_current', [
                    'title' => 'Показывать текущую страницу',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::checkbox('hide_on_front', [
                    'title' => 'Скрывать на главной',
                    'default' => 1,
                    'switch' => true,
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Внешний вид', [
            'icon' => 'bi bi-palette',
            'columns' => '12',
            'fields' => [
                \FieldFactory::select('separator', [
                    'title' => 'Разделитель',
                    'options' => [
                        'chevron' => '›',
                        'slash' => '/',
                        'arrow' => '→',
                        'dot' => '•',
                    ],
                    'default' => 'chevron'
                ]),
                \FieldFactory::icon('home_icon', [
                    'title' => 'Иконка главной',
                    'hint' => 'Оставьте пустым чтобы не показывать иконку',
                    'default' => '',
                ]),
                \FieldFactory::select('alignment', [
                    'title' => 'Выравнивание',
                    'options' => [
                        'left' => 'Слева',
                        'center' => 'По центру',
                        'right' => 'Справа',
                    ],
                    'default' => 'left'
                ]),
            ]
        ]);

        $fieldsets[] = new \Fieldset('Дополнительно', [
            'icon' => 'bi bi-three-dots',
            'columns' => '12',
            'fields' => [
                \FieldFactory::checkbox('enable_schema', [
                    'title' => 'Микроразметка',
                    'default' => 1,
                    'switch' => true,
                ]),
                \FieldFactory::string('custom_css_class', [
                    'title' => 'CSS класс',
                    'default' => '',
                    'placeholder' => 'my-breadcrumbs'
                ]),
            ]
        ]);

        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset) { ?>
            <div class="col-md-12">
                <?= $fieldset->render($settings) ?>
            </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validateSettings($settings): array {
        return [true, []];
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            return [];
        }

        return [
            'show_home' => isset($settings['show_home']) ? (int)$settings['show_home'] : 1,
            'show_current' => isset($settings['show_current']) ? (int)$settings['show_current'] : 1,
            'hide_on_front' => isset($settings['hide_on_front']) ? (int)$settings['hide_on_front'] : 1,
            'separator' => $settings['separator'] ?? 'chevron',
            'home_icon' => trim($settings['home_icon'] ?? ''),
            'alignment' => $settings['alignment'] ?? 'left',
            'enable_schema' => isset($settings['enable_schema']) ? (int)$settings['enable_schema'] : 1,
            'custom_css_class' => trim($settings['custom_css_class'] ?? '')
        ];
    }

    public function processFrontend($settings = [], $templateName = null): string {
        $breadcrumbs = null;
        if (class_exists('BreadcrumbsHelper')) {
            $breadcrumbs = BreadcrumbsHelper::getManager();
        }
        
        if (!$breadcrumbs || $breadcrumbs->isEmpty()) {
            return '';
        }
        
        if (!empty($settings['hide_on_front']) && $this->isFrontPage()) {
            return '';
        }
        
        $items = $breadcrumbs->getAll();
        
        if (empty($settings['show_home'])) {
            array_shift($items);
        }
        
        if (empty($settings['show_current'])) {
            array_pop($items);
        }
        
        if (empty($items)) {
            return '';
        }
        
        $data = $settings;
        $data['items'] = $items;
        $data['separator_char'] = $this->getSeparatorChar($settings);
        $data['container_class'] = $this->getContainerClass($settings);
        
        if (!empty($settings['enable_schema'])) {
            $data['schema_markup'] = $this->generateSchemaMarkup($items);
        }
        
        return parent::processFrontend($data, $templateName);
    }
    
    private function getSeparatorChar($settings) {
        $map = [
            'chevron' => '›',
            'slash' => '/',
            'arrow' => '→',
            'dot' => '•',
        ];
        
        return $map[$settings['separator']] ?? '›';
    }
    
    private function getContainerClass($settings) {
        $classes = ['tg-breadcrumbs'];
        
        if (!empty($settings['alignment'])) {
            $classes[] = 'text-' . $settings['alignment'];
        }
        
        if (!empty($settings['custom_css_class'])) {
            $classes[] = $settings['custom_css_class'];
        }
        
        return implode(' ', $classes);
    }
    
    private function generateSchemaMarkup($items) {
        $schemaItems = [];
        
        foreach ($items as $index => $item) {
            if (empty($item['url'])) {
                continue;
            }
            
            $schemaItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['title'],
                'item' => $this->getFullUrl($item['url'])
            ];
        }
        
        if (empty($schemaItems)) {
            return '';
        }
        
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $schemaItems
        ];
        
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }
    
    private function getFullUrl($path) {
        if (strpos($path, 'http') === 0) {
            return $path;
        }
        
        $base = defined('BASE_URL') ? BASE_URL : '';
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
    
    private function isFrontPage() {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = strtok($uri, '?');
        return $uri === '/' || $uri === '';
    }
}