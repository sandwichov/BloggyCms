<?php

namespace forms\actions;

/**
 * Действие получения структуры формы через AJAX
 */
class AdminGetStructure extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'ID формы не указан'
            ]);
            return;
        }
        
        try {
            $form = $this->formModel->getById($id);
            if (!$form) {
                throw new \Exception('Форма не найдена');
            }
            
            $structure = $form['structure'] ?? [];
            
            $this->jsonResponse([
                'success' => true,
                'structure' => $structure
            ]);
            
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}