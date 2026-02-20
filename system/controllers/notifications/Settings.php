<?php
namespace notifications;

class NotificationsSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Комментарии', [
                'icon' => 'bi bi-palette',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::select('variables', [
                        'title' => 'Показывать уведомления',
                        'default' => 'pending',
                        'options' => [
                            'all' => 'Все без исключения',
                            'pending' => 'Только требующие модерации',
                        ],
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