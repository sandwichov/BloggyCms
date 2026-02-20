<?php 

/**
 * Преобразует JSON-данные от редактора Editor.js в HTML-код
 * Поддерживает основные типы блоков: заголовки, параграфы, списки, изображения
 * 
 * @param string $content JSON-строка с данными от Editor.js
 * @return string HTML-код, сгенерированный из блоков
 */
function editorjs_to_html($content) {
    // Декодирование JSON
    $data = json_decode($content, true);
    if (!$data || !isset($data['blocks'])) return '';
    
    $html = '';
    foreach ($data['blocks'] as $block) {
        switch ($block['type']) {
            case 'header':
                $level = $block['data']['level'];
                $html .= "<h{$level}>{$block['data']['text']}</h{$level}>";
                break;
                
            case 'paragraph':
                $html .= "<p>{$block['data']['text']}</p>";
                break;
                
            case 'list':
                $tag = $block['data']['style'] === 'ordered' ? 'ol' : 'ul';
                $html .= "<{$tag}>";
                foreach ($block['data']['items'] as $item) {
                    $html .= "<li>{$item}</li>";
                }
                $html .= "</{$tag}>";
                break;
                
            case 'image':
                $url = $block['data']['file']['url'];
                $caption = $block['data']['caption'] ?? '';
                $html .= "<figure><img src='{$url}' alt='{$caption}'>";
                if ($caption) {
                    $html .= "<figcaption>{$caption}</figcaption>";
                }
                $html .= "</figure>";
                break;
        }
    }
    return $html;
}