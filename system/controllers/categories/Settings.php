<?php
namespace categories;

class CategoriesSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Общие настройки', [
                'icon' => 'bi bi-palette',
                'columns' => '6',
                'fields' => [
                    \FieldFactory::checkbox('show_stat', [
                        'title' => 'Отображать блок со статистикой над списком категорий',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_search', [
                        'title' => 'Отображать блок поиска над списком категорий',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_info', [
                        'title' => 'Отображать блок подсказок над списком категорий',
                        'hint' => 'В случайном порядке будут показываться полезные советы по работе с категориями.',
                        'default' => false,
                        'switch' => true
                    ]),
                    \FieldFactory::checkbox('show_stat_list', [
                        'title' => 'Отображать мини-статистику в списке категорий',
                        'hint' => 'Будут показаны количество постов категории и порядок отображения',
                        'default' => false,
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