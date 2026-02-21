<?php
/**
 * Profile Menu Template
 * Шаблон выпадающего меню профиля
 * 
 */

$currentUrl = $_SERVER['REQUEST_URI'];
?>

<div class="tg-profile-menu">
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
            
            $iconHtml = bloggy_icon($iconSet, $iconId, "$iconSize $iconSize", $iconColor, 'profile-menu-icon');
        }
        
        $itemClasses = array('profile-menu-item');
        if ($hasChildren) {
            $itemClasses[] = 'has-children';
        }
        if ($isActive) {
            $itemClasses[] = 'active';
        }
        if (!empty($itemClass)) {
            $itemClasses[] = html($itemClass, ENT_QUOTES, 'UTF-8');
        }
        
        $itemUrl = html($processedUrl, ENT_QUOTES, 'UTF-8');
        ?>
        
        <?php if ($hasChildren) { ?>
            <!-- Пункт с подменю -->
            <div class="<?php echo implode(' ', $itemClasses); ?>">
                <a href="#" 
                   class="profile-menu-link profile-menu-parent" 
                   role="button" 
                   aria-expanded="false">
                    <?php if ($iconHtml) { ?>
                        <?php echo $iconHtml; ?>
                    <?php } ?>
                    <span class="profile-menu-title"><?php echo $title; ?></span>
                    <?php echo bloggy_icon('bs', 'chevron-right', '14 14', 'currentColor', 'profile-menu-arrow'); ?>
                </a>
                
                <!-- Подменю первого уровня -->
                <div class="profile-submenu">
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
                            
                            $childIconHtml = bloggy_icon($childIconSet, $childIconId, "$childIconSize $childIconSize", $childIconColor, 'profile-submenu-icon');
                        }
                        
                        $childClasses = array('profile-submenu-item');
                        if ($childHasChildren) {
                            $childClasses[] = 'has-children';
                        }
                        if ($childIsActive) {
                            $childClasses[] = 'active';
                        }
                        if (!empty($childClass)) {
                            $childClasses[] = html($childClass, ENT_QUOTES, 'UTF-8');
                        }
                        
                        $childUrl = html($childProcessedUrl, ENT_QUOTES, 'UTF-8');
                        ?>
                        
                        <?php if ($childHasChildren) { ?>
                            <!-- Пункт с подменю второго уровня -->
                            <div class="<?php echo implode(' ', $childClasses); ?>">
                                <a href="#" 
                                   class="profile-submenu-link profile-menu-parent" 
                                   role="button" 
                                   aria-expanded="false">
                                    <?php if ($childIconHtml) { ?>
                                        <?php echo $childIconHtml; ?>
                                    <?php } ?>
                                    <span class="profile-menu-title"><?php echo $childTitle; ?></span>
                                    <?php echo bloggy_icon('bs', 'chevron-right', '12 12', 'currentColor', 'profile-menu-arrow'); ?>
                                </a>
                                
                                <!-- Подменю второго уровня -->
                                <div class="profile-submenu profile-submenu-nested">
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
                                            
                                            $subchildIconHtml = bloggy_icon($subchildIconSet, $subchildIconId, "$subchildIconSize $subchildIconSize", $subchildIconColor, 'profile-submenu-icon');
                                        }
                                        
                                        $subchildClasses = array('profile-submenu-item');
                                        if ($subchildIsActive) {
                                            $subchildClasses[] = 'active';
                                        }
                                        if (!empty($subchildClass)) {
                                            $subchildClasses[] = html($subchildClass, ENT_QUOTES, 'UTF-8');
                                        }
                                        
                                        $subchildUrl = html($subchildProcessedUrl, ENT_QUOTES, 'UTF-8');
                                        ?>
                                        
                                        <div class="<?php echo implode(' ', $subchildClasses); ?>">
                                            <a href="<?php echo $subchildUrl; ?>" 
                                               class="profile-submenu-link <?php echo $subchildIsActive ? 'active' : ''; ?>"
                                               target="<?php echo $subchildTarget; ?>">
                                                <?php if ($subchildIconHtml) { ?>
                                                    <?php echo $subchildIconHtml; ?>
                                                <?php } ?>
                                                <span class="profile-menu-title"><?php echo $subchildTitle; ?></span>
                                            </a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            
                        <?php } else { ?>
                            <!-- Обычный пункт подменю -->
                            <div class="<?php echo implode(' ', $childClasses); ?>">
                                <a href="<?php echo $childUrl; ?>" 
                                   class="profile-submenu-link <?php echo $childIsActive ? 'active' : ''; ?>"
                                   target="<?php echo $childTarget; ?>">
                                    <?php if ($childIconHtml) { ?>
                                        <?php echo $childIconHtml; ?>
                                    <?php } ?>
                                    <span class="profile-menu-title"><?php echo $childTitle; ?></span>
                                </a>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
            
        <?php } else { ?>
            <!-- Обычный пункт меню -->
            <div class="<?php echo implode(' ', $itemClasses); ?>">
                <a href="<?php echo $itemUrl; ?>" 
                   class="profile-menu-link <?php echo $isActive ? 'active' : ''; ?>"
                   target="<?php echo $target; ?>">
                    <?php if ($iconHtml) { ?>
                        <?php echo $iconHtml; ?>
                    <?php } ?>
                    <span class="profile-menu-title"><?php echo $title; ?></span>
                </a>
            </div>
        <?php } ?>
    <?php } ?>

</div>
