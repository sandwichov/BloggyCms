<?php

$settings = $this->getSettings();
$iconName = $settings['button_icon'] === 'chevron' ? 'chevron-up' : 'arrow-up';
$iconSet = 'bs';
$iconSize = $settings['button_size'] * 0.6;
?>
<button id="scroll-to-top" 
        class="scroll-to-top <?= $settings['button_position'] ?>" 
        style="background-color: <?= $settings['button_color'] ?>; 
               width: <?= $settings['button_size'] ?>px; 
               height: <?= $settings['button_size'] ?>px;">
    <?= bloggy_icon($iconSet, $iconName, $iconSize, '#ffffff') ?>
</button>