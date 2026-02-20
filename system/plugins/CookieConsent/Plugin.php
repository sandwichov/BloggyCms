<?php

class CookieConsentPlugin extends Plugin {
    protected function init() {
        $this->name = "Согласие с Cookies";
        $this->version = "1.0.0";
        $this->author = "BloggyCMS";
        $this->description = "Плагин для отображения уведомления о cookies и получения согласия пользователя";
    }
    
    public function getSystemName(): string {
        return 'CookieConsent';
    }

    public function getRoutes(): array {
        return [
            'settings' => [
                'controller' => 'AdminPlugins',
                'action' => 'settings',
                'admin' => true
            ],
            'save-consent' => [
                'controller' => 'CookieConsent',
                'action' => 'saveConsent',
                'admin' => false
            ]
        ];
    }

    public function activate() {
        $defaultSettings = [
            'position' => 'bottom',
            'theme' => 'light',
            'message' => 'Мы используем cookies для улучшения вашего опыта на сайте.',
            'button_text' => 'Понятно',
            'policy_link' => '/privacy-policy',
            'policy_text' => 'Узнать больше',
            'expiry_days' => 30,
            'enable_necessary' => true,
            'enable_analytics' => false,
            'enable_marketing' => false
        ];
        
        if (empty($this->getSettings())) {
            $this->saveSettings($defaultSettings);
        }
    }

    public function registerAssets(): void {
        $this->addCss('cookie-consent');
        $this->addJs('cookie-consent');
        
        $settings = $this->getSettings();
        
        $settings = array_merge([
            'position' => 'bottom',
            'theme' => 'light',
            'message' => 'Мы используем cookies для улучшения вашего опыта на сайте.',
            'button_text' => 'Принять',
            'policy_link' => '/privacy-policy',
            'policy_text' => 'Узнать больше',
            'expiry_days' => 30,
            'enable_analytics' => false,
            'enable_marketing' => false
        ], $settings);

        $this->addInlineJs("
            document.addEventListener('DOMContentLoaded', function() {
                CookieConsent.init({
                    position: '".addslashes($settings['position'])."',
                    theme: '".addslashes($settings['theme'])."',
                    message: '".addslashes($settings['message'])."',
                    buttonText: '".addslashes($settings['button_text'])."',
                    policyLink: '".addslashes($settings['policy_link'])."',
                    policyText: '".addslashes($settings['policy_text'])."',
                    expiryDays: ".intval($settings['expiry_days']).",
                    enableAnalytics: ".($settings['enable_analytics'] ? 'true' : 'false').",
                    enableMarketing: ".($settings['enable_marketing'] ? 'true' : 'false')."
                });
            });
        ");
    }
    
    public function renderFront(array $params = []): string {
        $settings = $this->getSettings();
        ob_start();
        include PLUGINS_PATH . '/CookieConsent/front/consent-bar.php';
        return ob_get_clean();
    }
    
    public function processShortcode(array $params = []): string {
        return $this->renderFront($params);
    }
}