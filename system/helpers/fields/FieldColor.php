<?php

/**
 * Поле типа "цвет" для системы полей
 * Использует библиотеку Pickr (https://github.com/simonwep/pickr)
 * 
 * @package Fields
 * @extends Field
 */
class FieldColor extends Field {
    
    /**
     * Рендерит HTML-код поля для выбора цвета
     * 
     * @param mixed $currentValue Текущее значение поля (HEX-код цвета)
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        add_admin_css('templates/default/admin/assets/css/pickr/monolith.min.css');
        add_admin_js('templates/default/admin/assets/js/pickr/pickr.min.js');
        
        $fieldId = 'color-picker-' . $this->name . '-' . uniqid();
        $pickrOptions = $this->getPickrOptions();
        $pickrOptionsJson = json_encode($pickrOptions);
        $config = [
            'iconsPath' => BASE_URL . '/templates/default/admin/icons/'
        ];
        
        static $configAdded = false;
        if (!$configAdded) {
            admin_bottom_js('<script>window.pickrConfig = ' . json_encode($config) . ';</script>');
            add_admin_js('templates/default/admin/assets/js/pickr/pickr-init.js');
            $configAdded = true;
        }
        
        $fieldHtml = sprintf(
            '<input type="text" 
                   name="settings[%s]" 
                   id="%s"
                   value="%s" 
                   class="form-control pickr-color-picker"
                   placeholder="#000000"
                   maxlength="7"
                   style="width: 150px; display: inline-block;"
                   data-pickr-options=\'%s\'>',
            $this->name,
            $fieldId,
            htmlspecialchars($value),
            htmlspecialchars($pickrOptionsJson)
        );
        
        return $this->renderFieldGroup($fieldHtml);
    }
    
    /**
     * Получает настройки для Pickr
     * 
     * @return array
     */
    protected function getPickrOptions() {
        $defaultOptions = [
            'showInput' => true,
            'showAlpha' => false,
            'allowEmpty' => true,
            'palette' => [
                '#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff', '#00ffff',
                '#000000', '#ffffff', '#808080', '#800000', '#808000', '#008000',
                '#800080', '#008080', '#000080', '#ffa500', '#ffc0cb', '#a52a2a',
                '#0d6efd', '#6610f2', '#6f42c1', '#d63384', '#dc3545', '#fd7e14',
                '#ffc107', '#198754', '#20c997', '#0dcaf0', '#6c757d', '#343a40'
            ]
        ];
        
        if (isset($this->options['pickr']) && is_array($this->options['pickr'])) {
            return array_merge($defaultOptions, $this->options['pickr']);
        }
        
        if (isset($this->options['preset'])) {
            switch ($this->options['preset']) {
                case 'basic':
                    return [
                        'showInput' => false,
                        'showAlpha' => false,
                        'palette' => []
                    ];
                    
                case 'advanced':
                    return [
                        'showInput' => true,
                        'showAlpha' => true,
                        'allowEmpty' => true,
                        'palette' => $defaultOptions['palette']
                    ];
                    
                case 'material':
                    return [
                        'showInput' => true,
                        'showAlpha' => false,
                        'palette' => [
                            '#f44336', '#e91e63', '#9c27b0', '#673ab7', '#3f51b5', '#2196f3',
                            '#03a9f4', '#00bcd4', '#009688', '#4caf50', '#8bc34a', '#cddc39',
                            '#ffeb3b', '#ffc107', '#ff9800', '#ff5722', '#795548', '#9e9e9e'
                        ]
                    ];
                    
                case 'minimal':
                    return [
                        'showInput' => false,
                        'showAlpha' => false,
                        'allowEmpty' => false,
                        'palette' => [
                            '#000000', '#666666', '#999999', '#cccccc', '#ffffff',
                            '#ff0000', '#00ff00', '#0000ff'
                        ]
                    ];
                    
                case 'website':
                    return [
                        'showInput' => true,
                        'showAlpha' => false,
                        'palette' => [
                            '#0d6efd', '#6610f2', '#6f42c1', '#d63384',
                            '#dc3545', '#fd7e14', '#ffc107', '#198754',
                            '#20c997', '#0dcaf0', '#6c757d', '#343a40'
                        ]
                    ];
                    
                case 'full':
                    return $defaultOptions;
            }
        }
        
        return $defaultOptions;
    }
}