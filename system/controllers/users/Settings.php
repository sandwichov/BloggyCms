<?php
namespace users;

class UsersSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Общие настройки', [
                'icon' => 'bi bi-palette',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('show_filter', [
                        'title' => 'Отображать фильтр для быстрого поиска пользователей',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_info', [
                        'title' => 'Отображать блок подсказок над списком категорий',
                        'hint' => 'В случайном порядке будут показываться полезные советы по работе с пользователями',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('admin_top', [
                        'title' => 'Отображать администраторов сайта сверху списка',
                        'default' => true,
                        'switch' => true
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