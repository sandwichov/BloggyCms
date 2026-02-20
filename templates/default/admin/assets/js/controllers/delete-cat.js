document.addEventListener('DOMContentLoaded', function() {
    const movePostsRadio = document.getElementById('move_posts');
    const deleteAllRadio = document.getElementById('delete_all');
    const targetCategorySelect = document.getElementById('target_category_select');
    const optionLabels = document.querySelectorAll('.option-label');
    
    function toggleCategorySelect() {
        if (movePostsRadio.checked) {
            targetCategorySelect.required = true;
            targetCategorySelect.disabled = false;
            targetCategorySelect.style.opacity = '1';
        } else {
            targetCategorySelect.required = false;
            targetCategorySelect.disabled = true;
            targetCategorySelect.style.opacity = '0.5';
        }
    }
    
    optionLabels.forEach(label => {
        label.addEventListener('click', function() {
            const radio = document.getElementById(this.getAttribute('for'));
            radio.checked = true;
            toggleCategorySelect();
        });
    });
    
    movePostsRadio.addEventListener('change', toggleCategorySelect);
    deleteAllRadio.addEventListener('change', toggleCategorySelect);
    
    toggleCategorySelect();
});

function confirmDeletion() {
    const deleteAllRadio = document.getElementById('delete_all');
    const targetCategorySelect = document.getElementById('target_category_select');
    
    if (deleteAllRadio.checked) {
        return confirm('ВНИМАНИЕ! Вы собираетесь удалить категорию и все посты в ней. Это действие нельзя отменить. Вы уверены?');
    } else {
        if (!targetCategorySelect.value) {
            alert('Пожалуйста, выберите категорию для перемещения постов');
            return false;
        }
        return confirm('Вы уверены, что хотите удалить категорию и переместить посты в выбранную категорию?');
    }
}