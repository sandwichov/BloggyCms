<?php
namespace comments;

class CommentsSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Общие настройки', [
                'icon' => 'bi bi-palette',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::number('max_depth', [
                        'title' => 'Максимальный уровень вложенности комментариев',
                        'default' => 4,
                        'hint' => 'Остальные комментарии будут скрыты под кнопкой "Показать еще"'
                    ]),
                    \FieldFactory::checkbox('show_groups', [
                        'title' => 'Показывать возле никнейма принадлежность пользователя к группе',
                        'default' => true,
                        'switch' => true,
                    ]),
                    \FieldFactory::checkbox('show_admin_badge', [
                        'title' => 'Показывать у Администратора специальный бейдж',
                        'default' => false,
                        'switch' => true,
                    ]),
                    \FieldFactory::string('title_badge', [
                        'title' => 'Текст бейджа',
                        'show' => 'field:show_admin_badge'
                    ]),
                    \FieldFactory::icon('icon_badge', [
                        'title' => 'Иконка бейджа',
                        'default' => 'bs:rocket',
                        'icons_page_url' => '/admin/icons',
                        'show' => 'field:show_admin_badge'
                    ]),
                    \FieldFactory::color('bg_badge', [
                        'title' => 'Фон бейджа',
                        'default' => '#007bff',
                        'preset' => 'basic',
                        'show' => 'field:show_admin_badge',
                    ]),
                    \FieldFactory::color('color_badge', [
                        'title' => 'Цвет текста',
                        'default' => '#ffffff',
                        'preset' => 'basic',
                        'show' => 'field:show_admin_badge',
                    ]),
                    \FieldFactory::checkbox('show_emodji', [
                        'title' => 'Включить эмоции у комментариев',
                        'default' => false,
                        'switch' => true,
                    ]),
                    \FieldFactory::repeater('emodji_list', [
                        'title' => 'Иконки эмоций',
                        'hint' => 'Добавьте нужные иконки. Например, с сервиса https://emojicopy.org/ru',
                        'show' => 'field:show_emodji',
                        'fields' => [
                            [
                                'name' => 'icon',
                                'title' => 'Вставьте иконку',
                                'type' => 'string'
                            ]
                        ]
                    ])
                ]
            ]),

            new \Fieldset('Переопределение заголовков', [
                'icon' => 'bi bi-input-cursor-text',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::string('z17', [
                        'title' => 'Комментарии',
                        'default' => 'Комментарии'
                    ]),
                    \FieldFactory::string('z18', [
                        'title' => 'Написать комментарий',
                        'default' => 'Написать комментарий'
                    ]),
                    \FieldFactory::string('z1', [
                        'title' => 'Комментариев пока нет',
                        'default' => 'Комментариев пока нет'
                    ]),
                    \FieldFactory::string('z2', [
                        'title' => 'Будьте первым, кто оставит комментарий!',
                        'default' => 'Будьте первым, кто оставит комментарий!'
                    ]),
                    \FieldFactory::string('z3', [
                        'title' => 'Показать ветку',
                        'default' => 'Показать ветку'
                    ]),
                    \FieldFactory::string('z4', [
                        'title' => 'На модерации',
                        'default' => 'На модерации'
                    ]),
                    \FieldFactory::string('z5', [
                        'title' => 'Вы',
                        'default' => 'Вы',
                        'hint' => 'Подсказывает пользователю, что это его комментарий'
                    ]),
                    \FieldFactory::string('z6', [
                        'title' => 'Ответить',
                        'default' => 'Ответить'
                    ]),
                    \FieldFactory::string('z7', [
                        'title' => 'Редактировать',
                        'default' => 'Редактировать'
                    ]),
                    \FieldFactory::string('z8', [
                        'title' => 'Удалить',
                        'default' => 'Удалить'
                    ]),
                    \FieldFactory::string('z10', [
                        'title' => 'Имя',
                        'default' => 'Имя'
                    ]),
                    \FieldFactory::string('z9', [
                        'title' => 'Введите ваше имя',
                        'default' => 'Введите ваше имя'
                    ]),
                    \FieldFactory::string('z11', [
                        'title' => 'Email',
                        'default' => 'Email'
                    ]),
                    \FieldFactory::string('z12', [
                        'title' => 'Введите ваш email',
                        'default' => 'Введите ваш email'
                    ]),
                    \FieldFactory::string('z13', [
                        'title' => 'Ваш email не будет опубликован',
                        'default' => 'Ваш email не будет опубликован'
                    ]),
                    \FieldFactory::string('z14', [
                        'title' => 'Вы комментируете как',
                        'default' => 'Вы комментируете как'
                    ]),
                    \FieldFactory::string('z15', [
                        'title' => 'Комментарий',
                        'default' => 'Комментарий'
                    ]),
                    \FieldFactory::string('z16', [
                        'title' => 'Напишите ваш комментарий...',
                        'default' => 'Напишите ваш комментарий...'
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