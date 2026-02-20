<?php

/**
 * Обрабатывает все шорткоды в контенте (системные + плагины)
 * Поддерживает множество типов шорткодов для CSS/JS, блоков, постов, условий и иконок
 * 
 * @param string $content Содержимое для обработки
 * @param array $posts Массив постов для шорткода {posts}
 * @param array $blocks Массив HTML-блоков для шорткода {add-block-*}
 * @return string Обработанное содержимое
 */
function process_shortcodes(string $content, array $posts = [], array $blocks = []): string {

    if (preg_match_all('/\{css\s+["\']([^"\']+)["\']\}/', $content, $matches)) {
        foreach ($matches[1] as $cssFile) {
            add_html_block_css($cssFile);
        }
        $content = preg_replace('/\{css\s+["\']([^"\']+)["\']\}/', '', $content);
    }

    if (preg_match_all('/\{js\s+["\']([^"\']+)["\']\}/', $content, $matches)) {
        foreach ($matches[1] as $jsFile) {
            add_html_block_js($jsFile);
        }
        $content = preg_replace('/\{js\s+["\']([^"\']+)["\']\}/', '', $content);
    }

    if (preg_match_all('/\{css-inline\}(.*?)\{\/css-inline\}/s', $content, $matches)) {
        foreach ($matches[1] as $inlineCss) {
            add_html_inline_css($inlineCss);
        }
        $content = preg_replace('/\{css-inline\}(.*?)\{\/css-inline\}/s', '', $content);
    }

    if (preg_match_all('/\{js-inline\}(.*?)\{\/js-inline\}/s', $content, $matches)) {
        foreach ($matches[1] as $inlineJs) {
            add_html_inline_js($inlineJs);
        }
        $content = preg_replace('/\{js-inline\}(.*?)\{\/js-inline\}/s', '', $content);
    }

    if (preg_match_all('/\{add-block-([^\}]+)\}/', $content, $matches)) {
        foreach ($matches[0] as $key => $fullMatch) {
            $blockSlug = $matches[1][$key];
            $blockContent = '';
            
            foreach ($blocks as $block) {
                if (isset($block['slug']) && $block['slug'] === $blockSlug) {
                    $blockContent = $block['content'];
                    break;
                }
            }
            
            $blockContent = process_shortcodes($blockContent, $posts, $blocks);
            
            $content = str_replace($fullMatch, $blockContent, $content);
        }
    }

    if (preg_match('/\{posts(?:\s+limit=(\d+))?\}(.*?)\{\/posts\}/s', $content, $matches)) {
        $limit = isset($matches[1]) ? (int)$matches[1] : null;
        $postTemplate = $matches[2];
        $postsContent = '';
        
        $postsToProcess = $limit ? array_slice($posts, 0, $limit) : $posts;

        foreach ($postsToProcess as $post) {
            $postContent = $postTemplate;

            $postUrl = BASE_URL . '/post/' . ($post['slug'] ?? '');
            $categoryUrl = BASE_URL . '/category/' . ($post['category_slug'] ?? '');
            $imageUrl = isset($post['featured_image']) 
                ? BASE_URL . '/uploads/images/' . $post['featured_image']
                : '';

            $replacements = [
                '{post-title}' => htmlspecialchars($post['title'] ?? ''),
                '{post-description}' => htmlspecialchars($post['excerpt'] ?? ''),
                '{post-id}' => $post['id'] ?? '',
                '{post-slug}' => $postUrl,
                '{post-category}' => htmlspecialchars($post['category_name'] ?? ''),
                '{category-slug}' => $categoryUrl,
                '{post-image}' => $imageUrl,
                '{post-excerpt}' => nl2br(htmlspecialchars($post['excerpt'] ?? '')),
                '{post-meta}' => htmlspecialchars($post['meta_description'] ?? ''),
                '{post-tags}' => htmlspecialchars($post['tags'] ?? ''),
                '{post-created:date}' => format_date($post['created_at'] ?? ''),
                '{post-updated:date}' => format_date($post['updated_at'] ?? ''),
                '{post-created:ago}' => time_ago($post['created_at'] ?? ''),
                '{post-updated:ago}' => time_ago($post['updated_at'] ?? ''),
                '{post-created}' => htmlspecialchars($post['created_at'] ?? ''),
                '{post-updated}' => htmlspecialchars($post['updated_at'] ?? '')
            ];

            foreach ($replacements as $search => $replace) {
                $postContent = str_replace($search, $replace, $postContent);
            }

            $postsContent .= $postContent;
        }

        $content = str_replace($matches[0], $postsContent, $content);
    }

    if (preg_match_all('/\{if:([^\}]+)\}(.*?)\{\/if:\1\}/s', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $settingName = $match[1];
            $conditionalContent = $match[2];
            
            $shouldShow = false;
            
            if (isset($settings[$settingName]) && $settings[$settingName]) {
                $shouldShow = true;
            } elseif (isset($GLOBALS['block_settings'][$settingName]) && $GLOBALS['block_settings'][$settingName]) {
                $shouldShow = true;
            }
            
            $content = str_replace($fullMatch, $shouldShow ? $conditionalContent : '', $content);
        }
    }

    // Обрабатка иконок {icon "set:name" size="24" color="#fff"}
    if (preg_match_all('/\{icon[\s]+[\'"]([^:]+):([^\'""]+)[\'"]((?:\s+(?:size=[\'"][\d\s]+[\'"]|color=[\'"][^\'""]+[\'"]))*)\}/', $content, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $fullMatch = $match[0];
            $set = $match[1];
            $icon = $match[2];
            $attrs = $match[3];
            $size = preg_match('/size=[\'"]([\d\s]+)[\'"]/', $attrs, $sizeMatch) ? $sizeMatch[1] : null;
            $color = preg_match('/color=[\'"]([^\'""]+)[\'"]/', $attrs, $colorMatch) ? $colorMatch[1] : null;

            $content = str_replace($fullMatch, bloggy_icon($set, $icon, $size, $color), $content);
        }
    }

    $content = preg_replace_callback(
        '/\{front-image:([^:}]+)(?::([^}]+))?\}/i',
        function($matches) {
            return front_image($matches[1], $matches[2] ?? '');
        },
        $content
    );

    if (class_exists('Shortcodes')) {
        $content = Shortcodes::process($content);
    }

    $content = process_plugin_shortcodes($content);

    return $content;
}