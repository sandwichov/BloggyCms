<?php add_admin_css('templates/default/admin/assets/css/controllers/blocked.css'); ?>

<div class="blocked-container">
    <div class="blocked-icon">🚫</div>
    <div class="blocked-title">ДОСТУП ЗАБЛОКИРОВАН</div>
    <div class="blocked-message">Превышено максимальное количество попыток входа</div>
    <div class="blocked-message">Страница авторизации временно недоступна</div>
    <div class="blocked-time">
        Доступ будет восстановлен:<br>
        <?= date('d.m.Y в H:i:s', $unlockTime) ?>
    </div>
    <div class="blocked-message">
        Осталось времени: <strong><?= $remainingMinutes ?> минут</strong>
    </div>
    <div class="attempts-info">
        Система безопасности заблокировала доступ для защиты от несанкционированного доступа
    </div>
</div>

<?php ob_start(); ?>
<script>
    setTimeout(function() {
        location.reload();
    }, 60000);
</script>
<?php admin_bottom_js(ob_get_clean()); ?>