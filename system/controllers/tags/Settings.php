<?php
namespace tags;

class TagSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Настройки отображения', [
                'icon' => 'bi bi-eye',
                'fields' => [
                    \FieldFactory::string('tag_prefix', [
                        'title' => 'Символ перед тегом',
                        'hint' => 'Символ, который будет отображаться перед каждым тегом на фронте',
                        'default' => '#',
                        'placeholder' => '#',
                        'attributes' => ['maxlength' => '5']
                    ]),
                    
                    \FieldFactory::number('min_posts_to_show', [
                        'title' => 'Не показывать тег, если количество постов меньше',
                        'hint' => 'Теги с меньшим количеством постов не будут отображаться на странице всех тегов site.ru/tags. Оставьте 0 чтобы отображать все теги.',
                        'default' => 1,
                        'min' => 0,
                    ]),

                    \FieldFactory::number('cont_tags_in_front', [
                        'title' => 'Количество тегов на одной странице',
                        'hint' => 'Сколько тегов отображать на site.ru/tags. Иначе - сработает пагиация',
                        'default' => 12,
                        'min' => 0
                    ]),

                    \FieldFactory::select('tags_order', [
                        'title' => 'Порядок сортировки тегов',
                        'hint' => 'Как сортировать теги на странице всех тегов',
                        'default' => 'name',
                        'options' => [
                            'name' => 'По имени',
                            'posts_count' => 'По количеству постов', 
                            'created_at' => 'По дате создания'
                        ]
                    ]),

                    \FieldFactory::image('default_tag_image', [
                        'title' => 'Дефолтное изображение тега',
                        'upload_path' => 'uploads/settings/tags/'
                    ]),

                ]
            ]),
            
            new \Fieldset('Создание поста', [
                'icon' => 'bi bi-gear',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::number('max_tags_per_post', [
                        'title' => 'Максимум тегов для поста',
                        'hint' => 'Максимальное количество тегов, которые можно добавить к одному посту',
                        'default' => 10,
                        'min' => 1,
                        'max' => 50
                    ]),
                    \FieldFactory::checkbox('show_info', [
                        'title' => 'Отображать блок подсказок над списком тегов',
                        'hint' => 'В случайном порядке будут показываться полезные советы по работе с тегами',
                        'default' => false,
                        'switch' => true
                    ])
                ]
            ])
        ];
        
        ob_start();
        ?>
        <div class="row">
            <?php foreach ($fieldsets as $fieldset): ?>
            <div class="col-md-12">
                <?= $fieldset->render($currentSettings) ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}