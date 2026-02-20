<?php

class ScrollToTopPlugin extends Plugin {
    protected function init() {
        $this->name = "Кнопка «Вверх»";
        $this->version = "1.0.0";
        $this->author = "BloggyCMS";
        $this->description = "Добавляет на сайт кнопку для быстрой прокрутки страницы вверх";
        $this->addCss('scroll-to-top');
        $this->addJs('scroll-to-top');
    }
    
    public function getSystemName(): string {
        return 'ScrollToTop';
    }

    public function getRoutes(): array {
        return [
            'settings' => [
                'controller' => 'AdminPlugins',
                'action' => 'settings',
                'admin' => true
            ]
        ];
    }

    public function activate() {
        $defaultSettings = [
            'button_position' => 'right',
            'button_color' => '#007bff',
            'show_after_scroll' => 300,
            'animation_speed' => 800,
            'button_size' => 40,
            'button_icon' => 'arrow'
        ];
        
        if (empty($this->getSettings())) {
            $this->saveSettings($defaultSettings);
        }
    }

    public function registerAssets(): void {
        parent::registerAssets();
        
        $settings = $this->getSettings();
        $this->addInlineJs("
            document.addEventListener('DOMContentLoaded', function() {
                ScrollToTop.init({
                    position: \"".$settings['button_position']."\",
                    showAfter: ".$settings['show_after_scroll'].",
                    animationSpeed: ".$settings['animation_speed']."
                });
            });
        ");
    }
    
    public function renderFront(array $params = []): string {
        $settings = $this->getSettings();
        if (!empty($params)) {
            $settings = array_merge($settings, $params);
        }
        
        $iconName = $settings['button_icon'] === 'chevron' ? 'chevron-up' : 'arrow-up';
        $iconSize = $settings['button_size'] * 0.6;
        
        return sprintf(
            '<button id="scroll-to-top" 
                    class="scroll-to-top %s" 
                    style="background-color: %s; 
                           width: %spx; 
                           height: %spx;">
                    %s
                </button>',
            htmlspecialchars($settings['button_position']),
            htmlspecialchars($settings['button_color']),
            (int)$settings['button_size'],
            (int)$settings['button_size'],
            bloggy_icon('bs', $iconName, $iconSize, '#ffffff')
        );
    }
    
    public function processShortcode(array $params = []): string {
        return $this->renderFront($params);
    }
}