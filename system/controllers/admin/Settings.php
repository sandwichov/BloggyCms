<?php
namespace admin;

class AdminSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Блоки статистики', [
                'icon' => 'bi bi-bar-chart-fill',
                'columns' => '4',
                'fields' => [
                    \FieldFactory::checkbox('all_posts', [
                        'title' => 'Общее количество постов',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('categories', [
                        'title' => 'Общее количество категорий',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('tags', [
                        'title' => 'Общее количество тегов',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('comments', [
                        'title' => 'Общее количество комментариев',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('users', [
                        'title' => 'Общее количество пользователей',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('pages', [
                        'title' => 'Общее количество страниц',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('content_blocks', [
                        'title' => 'Общее количество контент-блоков',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('plugins', [
                        'title' => 'Общее количество плагинов',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_button', [
                        'title' => 'Показывать кнопку быстрого перехода к контроллеру',
                        'hint' => 'У каждой карточки появится кнопка для быстрого перехода к контроллеру',
                        'default' => false,
                        'switch' => true
                    ]),
                ]
            ]),

            new \Fieldset('Статистика постов', [
                'icon' => 'bi bi-pencil-fill',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('last_posts', [
                        'title' => 'Показывать последние опубликованные посты',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('popular_posts', [
                        'title' => 'Показывать  самые просматриваемые посты',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('comments_posts', [
                        'title' => 'Показывать  самые комментируемые посты',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_drafts', [
                        'title' => 'Показывать черновики',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::number('count_posts', [
                        'title' => 'Количество постов в блоках статистики',
                        'default' => 4,
                        'max' => 10
                    ]),
                ]
            ]),

            new \Fieldset('Быстрые действия', [
                'icon' => 'bi bi-lightning-charge-fill',
                'columns' => '3',
                'fields' => [
                    \FieldFactory::checkbox('add_post', [
                        'title' => 'Создать пост',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_page', [
                        'title' => 'Создать страницу',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_category', [
                        'title' => 'Создать категорию',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_tag', [
                        'title' => 'Создать тег',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_user', [
                        'title' => 'Создать пользователя',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_content_block', [
                        'title' => 'Создать контент-блок',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_field', [
                        'title' => 'Создать поле',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('add_form', [
                        'title' => 'Создать форму',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::select('position_btn', [
                        'title' => 'Позиция кнопки', 
                        'default' => 'bottom-right',
                        'options' => [
                            'bottom-right' => 'Справа снизу',
                            'bottom-right-center' => 'Снизу по центру'
                        ]
                    ]),
                    \FieldFactory::select('color_btn', [
                        'title' => 'Стиль кнопки', 
                        'default' => 'primary',
                        'options' => [
                            'success text-dark' => 'Success',
                            'primary' => 'Primary',
                            'dark' => 'Dark',
                            'danger' => 'Danger',
                            'warning' => 'Warning',
                        ]
                    ]),
                    \FieldFactory::alert('field_name', [
                        'title' => '<strong>Подсказка</strong>',
                        'hint' => 'Чтобы вообще не отображать кнопку - отключите все быстрые действия',
                        'type' => 'success',
                        'icon' => 'info-circle',
                        'full_width' => true
                    ])
                ]
            ]),

            new \Fieldset('Поисковые запросы', [
                'icon' => 'bi bi-search',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('show_search', [
                        'title' => 'Отображать историю поисковых запросов',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_popular_search', [
                        'title' => 'Отображать популярные поисковые запросы',
                        'default' => true,
                        'switch' => true,
                        'show' => 'field:show_search'
                    ]),
                ]
            ]),

            new \Fieldset('Внешний вид', [
                'icon' => 'bi bi-palette',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::image('bg_panel', [
                        'title' => 'Фоновое изображение боковой панели',
                        'upload_path' => 'uploads/settings/admin/'
                    ]),
                    \FieldFactory::select('notification_position', [
                        'title' => 'Положение уведомлений системы',
                        'options' => [
                            'top-left' => 'Сверху слева',
                            'top-right' => 'Сверху справа',
                            'bottom-left' => 'Снизу слева',
                            'bottom-right' => 'Снизу справа'
                        ],
                        'default' => 'top-right'
                    ]),
                ]
            ]),
            
        ];
        
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset) { ?>
            <div class="col-md-12">
                <?= $fieldset->render($currentSettings) ?>
            </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
}