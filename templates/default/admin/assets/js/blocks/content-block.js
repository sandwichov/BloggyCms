document.addEventListener('DOMContentLoaded', function() {
    function updateContentBlockPreview(blockId, blockElement) {
        const contentBlockSelect = blockElement.querySelector('#content-block-select');
        const previewDiv = blockElement.querySelector('#content-block-preview');
        const previewName = blockElement.querySelector('#preview-block-name');
        const previewId = blockElement.querySelector('#preview-block-id');
        
        if (contentBlockSelect && previewDiv) {
            contentBlockSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const blockId = this.value;
                const blockName = selectedOption ? selectedOption.getAttribute('data-name') : '';
                
                const nameInput = blockElement.querySelector('#content-block-name');
                if (nameInput) {
                    nameInput.value = blockName;
                }
                
                if (blockId && previewName && previewId) {
                    previewName.textContent = blockName;
                    previewId.textContent = blockId;
                    previewDiv.style.display = 'block';
                } else {
                    previewDiv.style.display = 'none';
                }
                
                if (typeof updatePostBlockPreview === 'function') {
                    updatePostBlockPreview(blockId);
                }
            });
        }
    }
    
    document.querySelectorAll('.post-block[data-block-type="ContentBlockPostBlock"]').forEach(blockElement => {
        const blockId = blockElement.getAttribute('data-block-id');
        updateContentBlockPreview(blockId, blockElement);
    });
    
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if (node.classList && node.classList.contains('post-block')) {
                            const blockType = node.getAttribute('data-block-type');
                            if (blockType === 'ContentBlockPostBlock') {
                                const blockId = node.getAttribute('data-block-id');
                                updateContentBlockPreview(blockId, node);
                            }
                        }
                        
                        const newBlocks = node.querySelectorAll ? node.querySelectorAll('.post-block[data-block-type="ContentBlockPostBlock"]') : [];
                        newBlocks.forEach(blockElement => {
                            const blockId = blockElement.getAttribute('data-block-id');
                            updateContentBlockPreview(blockId, blockElement);
                        });
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
});