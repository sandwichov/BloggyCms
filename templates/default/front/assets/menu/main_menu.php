<?php
/**
 * Main Menu Template
 * Шаблон главного меню
 * 
 */

$currentUrl = $_SERVER['REQUEST_URI'];
$uniqueId = uniqid('menu-');
?>

<ul class="tg-menu" id="<?php echo $uniqueId; ?>">
    <?php foreach ($menuItems as $item) { ?>
        <?php
        $processedUrl = MenuRenderer::processUrl($item['url'] ?? '');
        $hasChildren = !empty($item['children']);
        $isActive = MenuRenderer::isActiveUrl($processedUrl, $currentUrl);
        
        $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $target = $item['target'] ?? '_self';
        $itemClass = $item['class'] ?? '';
        
        $iconHtml = '';
        if (!empty($item['icon']) && is_array($item['icon']) && !empty($item['icon']['id'])) {
            $iconSet = $item['icon']['set'] ?? 'bs';
            $iconId = $item['icon']['id'];
            $iconSize = !empty($item['icon']['size']) ? $item['icon']['size'] : 18;
            $iconColor = !empty($item['icon']['color']) ? $item['icon']['color'] : 'currentColor';
            
            $iconHtml = bloggy_icon($iconSet, $iconId, "$iconSize $iconSize", $iconColor, 'menu-icon');
        }
        
        $liClasses = array('tg-menu-item');
        if ($hasChildren) {
            $liClasses[] = 'has-children';
        }
        if ($isActive) {
            $liClasses[] = 'active';
        }
        if (!empty($itemClass)) {
            $liClasses[] = html($itemClass, ENT_QUOTES, 'UTF-8');
        }
        
        $itemUrl = html($processedUrl, ENT_QUOTES, 'UTF-8');
        ?>
        
        <li class="<?php echo implode(' ', $liClasses); ?>">
            <?php if ($hasChildren) { ?>
                <a href="#" 
                   class="tg-menu-link tg-menu-parent" 
                   role="button" 
                   aria-expanded="false"
                   data-toggle="dropdown">
                    <?php if ($iconHtml) { ?>
                        <?php echo $iconHtml; ?>
                    <?php } ?>
                    <span class="menu-title"><?php echo $title; ?></span>
                    <?php echo bloggy_icon('bs', 'chevron-down', '16 16', 'currentColor', 'menu-arrow'); ?>
                </a>

                <ul class="tg-submenu">
                    <?php foreach ($item['children'] as $child) { ?>
                        <?php
                        $childProcessedUrl = MenuRenderer::processUrl($child['url'] ?? '');
                        $childHasChildren = !empty($child['children']);
                        $childIsActive = MenuRenderer::isActiveUrl($childProcessedUrl, $currentUrl);
                        
                        $childTitle = html($child['title'] ?? '', ENT_QUOTES, 'UTF-8');
                        $childTarget = $child['target'] ?? '_self';
                        $childClass = $child['class'] ?? '';
                        $childIconHtml = '';
                        if (!empty($child['icon']) && is_array($child['icon']) && !empty($child['icon']['id'])) {
                            $childIconSet = $child['icon']['set'] ?? 'bs';
                            $childIconId = $child['icon']['id'];
                            $childIconSize = !empty($child['icon']['size']) ? $child['icon']['size'] : 16;
                            $childIconColor = !empty($child['icon']['color']) ? $child['icon']['color'] : 'currentColor';
                            
                            $childIconHtml = bloggy_icon($childIconSet, $childIconId, "$childIconSize $childIconSize", $childIconColor, 'submenu-icon');
                        }
                        
                        $childLiClasses = array('tg-submenu-item');
                        if ($childHasChildren) {
                            $childLiClasses[] = 'has-children';
                        }
                        if ($childIsActive) {
                            $childLiClasses[] = 'active';
                        }
                        if (!empty($childClass)) {
                            $childLiClasses[] = html($childClass, ENT_QUOTES, 'UTF-8');
                        }
                        
                        $childUrl = html($childProcessedUrl, ENT_QUOTES, 'UTF-8');
                        ?>
                        
                        <li class="<?php echo implode(' ', $childLiClasses); ?>">
                            <?php if ($childHasChildren) { ?>
                                <a href="#" 
                                   class="tg-submenu-link tg-menu-parent" 
                                   role="button" 
                                   aria-expanded="false">
                                    <?php if ($childIconHtml) { ?>
                                        <?php echo $childIconHtml; ?>
                                    <?php } ?>
                                    <span class="menu-title"><?php echo $childTitle; ?></span>
                                    <?php echo bloggy_icon('bs', 'chevron-right', '14 14', 'currentColor', 'menu-arrow'); ?>
                                </a>
                                
                                <ul class="tg-submenu tg-submenu-nested">
                                    <?php foreach ($child['children'] as $subchild) { ?>
                                        <?php
                                        $subchildProcessedUrl = MenuRenderer::processUrl($subchild['url'] ?? '');
                                        $subchildIsActive = MenuRenderer::isActiveUrl($subchildProcessedUrl, $currentUrl);
                                        
                                        $subchildTitle = html($subchild['title'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $subchildTarget = $subchild['target'] ?? '_self';
                                        $subchildClass = $subchild['class'] ?? '';
                                        
                                        $subchildIconHtml = '';
                                        if (!empty($subchild['icon']) && is_array($subchild['icon']) && !empty($subchild['icon']['id'])) {
                                            $subchildIconSet = $subchild['icon']['set'] ?? 'bs';
                                            $subchildIconId = $subchild['icon']['id'];
                                            $subchildIconSize = !empty($subchild['icon']['size']) ? $subchild['icon']['size'] : 16;
                                            $subchildIconColor = !empty($subchild['icon']['color']) ? $subchild['icon']['color'] : 'currentColor';
                                            
                                            $subchildIconHtml = bloggy_icon($subchildIconSet, $subchildIconId, "$subchildIconSize $subchildIconSize", $subchildIconColor, 'submenu-icon');
                                        }
                                        
                                        $subchildLiClasses = array('tg-submenu-item');
                                        if ($subchildIsActive) {
                                            $subchildLiClasses[] = 'active';
                                        }
                                        if (!empty($subchildClass)) {
                                            $subchildLiClasses[] = html($subchildClass, ENT_QUOTES, 'UTF-8');
                                        }
                                        
                                        $subchildUrl = html($subchildProcessedUrl, ENT_QUOTES, 'UTF-8');
                                        ?>
                                        
                                        <li class="<?php echo implode(' ', $subchildLiClasses); ?>">
                                            <a href="<?php echo $subchildUrl; ?>" 
                                               class="tg-submenu-link <?php echo $subchildIsActive ? 'active' : ''; ?>"
                                               target="<?php echo $subchildTarget; ?>">
                                                <?php if ($subchildIconHtml) { ?>
                                                    <?php echo $subchildIconHtml; ?>
                                                <?php } ?>
                                                <span class="menu-title"><?php echo $subchildTitle; ?></span>
                                            </a>
                                        </li>
                                    <?php } ?>
                                </ul>
                                
                            <?php } else { ?>
                                <a href="<?php echo $childUrl; ?>" 
                                   class="tg-submenu-link <?php echo $childIsActive ? 'active' : ''; ?>"
                                   target="<?php echo $childTarget; ?>">
                                    <?php if ($childIconHtml) { ?>
                                        <?php echo $childIconHtml; ?>
                                    <?php } ?>
                                    <span class="menu-title"><?php echo $childTitle; ?></span>
                                </a>
                            <?php } ?>
                        </li>
                    <?php } ?>
                </ul>
                
            <?php } else { ?>
                <a href="<?php echo $itemUrl; ?>" 
                   class="tg-menu-link <?php echo $isActive ? 'active' : ''; ?>"
                   target="<?php echo $target; ?>">
                    <?php if ($iconHtml) { ?>
                        <?php echo $iconHtml; ?>
                    <?php } ?>
                    <span class="menu-title"><?php echo $title; ?></span>
                </a>
            <?php } ?>
        </li>
    <?php } ?>
</ul>

<button type="button" class="tg-mobile-toggle" aria-label="Меню" data-target="#<?php echo $uniqueId; ?>">
    <span class="toggle-bar"></span>
    <span class="toggle-bar"></span>
    <span class="toggle-bar"></span>
</button>