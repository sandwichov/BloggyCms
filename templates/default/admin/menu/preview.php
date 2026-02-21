<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'eye', '24', '#000', 'me-2'); ?>
            Предпросмотр меню: <?php echo html($menu['name']); ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/menu/edit/<?php echo $menu['id']; ?>" class="btn btn-outline-primary me-2">
                <?php echo bloggy_icon('bs', 'pencil', '16', '#000', 'me-2'); ?>
                Редактировать
            </a>
            <a href="<?php echo ADMIN_URL; ?>/menu" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                Назад
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0">
            <h5 class="card-title mb-0">Информация о меню</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Название:</strong> <?php echo html($menu['name']); ?>
                </div>
                <div class="col-md-4">
                    <strong>Шаблон:</strong> <code><?php echo html($menu['template']); ?></code>
                </div>
                <div class="col-md-4">
                    <strong>Тема:</strong> <code><?php echo html($currentTheme); ?></code>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <strong>Путь к шаблону:</strong> 
                    <code>templates/<?php echo html($currentTheme); ?>/front/assets/menu/<?php echo html($menu['template']); ?>.php</code>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0">
            <h5 class="card-title mb-0">Предпросмотр</h5>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Шаблон:</strong> <code><?php echo html($menu['template']); ?></code>
                | <strong>Тема:</strong> <code><?php echo html($currentTheme); ?></code>
            </div>
            
            <div class="border rounded p-4 bg-light">
                <h6 class="text-muted mb-3">Внешний вид меню:</h6>
                <?php
                if (file_exists($templateFile)) {
                    echo MenuRenderer::render($menu['name']);
                } else {
                    echo '<div class="alert alert-warning">';
                    echo '<h6>' . bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2') . 'Шаблон меню не найден</h6>';
                    echo '<p class="mb-1">Файл: <code>' . html($templateFile) . '</code></p>';
                    echo '<p class="mb-0">Убедитесь, что файл существует в текущей теме.</p>';
                    echo '</div>';
                    echo '<div class="mt-3">';
                    echo '<h6>Структура меню (для отладки):</h6>';
                    echo '<pre class="bg-dark text-light p-3 rounded small"><code>' . html(json_encode($structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</code></pre>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-0">
            <h5 class="card-title mb-0">Структура меню (JSON)</h5>
        </div>
        <div class="card-body">
            <pre class="bg-dark text-light p-3 rounded small"><code><?php echo html(json_encode($structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); ?></code></pre>
        </div>
    </div>
</div>