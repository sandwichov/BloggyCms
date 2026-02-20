<?php
namespace auth;

class AdminSettings {
    public static function getForm($currentSettings) {
        $fieldsets = [
            new \Fieldset('Авторизация', [
                'icon' => 'bi bi-person',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::checkbox('disable_restore', [
                        'title' => 'Запретить восстановление пароля',
                        'hint' => 'Пользователь не сможет восстановить свои учетные данные, если забудет их',
                        'default' => false,
                        'switch' => true
                    ]),
                    \FieldFactory::select('auth_redirect', [
                        'title' => 'После успешной авторизации:',
                        'default' => 'show_profile',
                        'options' => [
                            'show_profile' => 'Открыть профиль',
                            'show_index' => 'Открыть главную страницу',
                        ],
                    ]),
                ]
            ]),

            new \Fieldset('Регистрация', [
                'icon' => 'bi bi-person-plus',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::checkbox('enable_register', [
                        'title' => 'Отключить регистрацию пользователей на сайте',
                        'default' => true,
                        'switch' => true
                    ]),
                    \FieldFactory::string('disable_register_reason', [
                        'title' => 'Сообщение о невозможности регистрации',
                        'default' => 'Регистрация новых пользователей временно остановлена',
                        'show' => 'field:enable_register',
                    ]),
                ]
            ]),

            new \Fieldset('Доступ к админ-панели', [
                'icon' => 'bi bi-gear',
                'columns' => '12',
                'fields' => [
                    \FieldFactory::checkbox('show_qa', [
                        'title' => 'Использовать контрольные фразы для дополнительной защиты',
                        'default' => false,
                        'switch' => true
                    ]),
                    \FieldFactory::select('qa_param', [
                        'title' => 'Условие для показа', 
                        'hint' => 'Выберите, в каких случаях будут отображаться дополнительные вопросы для авторизации',
                        'default' => 'option2',
                        'options' => [
                            'opt1' => 'При неверной паре логин/пароль',
                            'opt2' => 'При смене последнего IP адреса',
                            'opt3' => 'Отображать всегда'
                        ],
                        'required' => true,
                        'show' => 'field:show_qa'
                    ]),
                    \FieldFactory::repeater('words_array', [
                        'title' => 'Укажите контрольные вопросы и ответы',
                        'hint' => 'При авторизации в панели управления будут показаны эти вопросы совместно с парой логин/пароль',
                        'fields' => [
                            [
                                'name' => 'question',
                                'title' => 'Вопрос',
                                'type' => 'string',
                                'hint' => 'Укажите ваш вопрос',
                            ],
                            [
                                'name' => 'answer',
                                'title' => 'Ответ',
                                'type' => 'string',
                                'hint' => 'Укажите ваш ответ',
                            ],
                        ],
                        'show' => 'field:show_qa'
                    ]),
                    \FieldFactory::number('count_auth', [
                        'title' => 'Максимальное количество попыток авторизации', 
                        'hint' => 'После использования всех попыток авторизации страница станет недоступна для ввода данных',
                        'default' => '3',
                        'required' => true,
                        'max' => 5,
                        'show' => 'field:show_qa'
                    ]),
                    \FieldFactory::number('count_time', [
                        'title' => 'Время, в течение которого страница будет недоступна', 
                        'hint' => 'Укажите значение в минутах',
                        'default' => '20',
                        'required' => true,
                        'show' => 'field:show_qa'
                    ]),
                    \FieldFactory::alert('auth_info', [
                        'title' => 'Важно!',
                        'hint' => 'Отнеситесь к этим настройкам с особой осторожностью! Есть вероятность потери доступа, если Вы забудете данные.',
                        'type' => 'danger',
                        'icon' => 'info-circle',
                        'show' => 'field:show_qa',
                        'full_width' => true
                    ])
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