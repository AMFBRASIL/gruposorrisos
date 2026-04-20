<?php
require_once __DIR__ . '/../config/config.php';

/**
 * Classe para upload de arquivos (armazenamento local)
 * Versão simplificada sem integração S3
 */
class S3Uploader {
    private $uploadBaseDir;
    
    public function __construct() {
        // Sempre usar armazenamento local
        $this->uploadBaseDir = __DIR__ . '/../uploads/';
        
        // Criar diretório base se não existir
        if (!is_dir($this->uploadBaseDir)) {
            mkdir($this->uploadBaseDir, 0755, true);
        }
    }
    
    /**
     * Upload de arquivo (armazenamento local)
     * 
     * @param string $filePath Caminho temporário do arquivo
     * @param string $fileName Nome do arquivo
     * @param string $folder Pasta de destino (ex: 'notas-fiscais')
     * @return array ['success' => bool, 'url' => string, 'error' => string]
     */
    public function uploadFile($filePath, $fileName, $folder = 'uploads') {
        return $this->uploadLocal($filePath, $fileName, $folder);
    }
    
    private function uploadLocal($filePath, $fileName, $folder) {
        $uploadDir = $this->uploadBaseDir . $folder . '/';
        
        // Criar diretório se não existir
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return [
                    'success' => false,
                    'error' => 'Erro ao criar diretório de upload: ' . $uploadDir
                ];
            }
        }
        
        // Gerar nome único para evitar conflitos
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $uniqueName = uniqid() . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($fileName, '.' . $extension)) . '.' . $extension;
        $destination = $uploadDir . $uniqueName;
        
        // Verificar se é um arquivo temporário do upload ou um arquivo existente
        if (is_uploaded_file($filePath)) {
            // É um arquivo enviado via POST
            $moved = move_uploaded_file($filePath, $destination);
        } else {
            // É um arquivo já existente (cópia)
            $moved = copy($filePath, $destination);
        }
        
        if ($moved) {
            // Retornar URL relativa para acesso via web (garantir que começa com /)
            $url = '/uploads/' . $folder . '/' . $uniqueName;
            // Remover barras duplicadas
            $url = preg_replace('#/+#', '/', $url);
            
            return [
                'success' => true,
                'url' => $url,
                'key' => $folder . '/' . $uniqueName,
                'fileName' => $uniqueName,
                'path' => $destination
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Erro ao salvar arquivo localmente. Verifique permissões do diretório: ' . $uploadDir
        ];
    }
    
    /**
     * Validar tipo de arquivo
     * 
     * @param string $fileName Nome do arquivo
     * @param array $allowedTypes Tipos permitidos (ex: ['pdf', 'jpg', 'png'])
     * @return bool
     */
    public static function validateFileType($fileName, $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif']) {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        return in_array($extension, $allowedTypes);
    }
    
    /**
     * Validar tamanho do arquivo
     * 
     * @param int $fileSize Tamanho em bytes
     * @param int $maxSize Tamanho máximo em MB
     * @return bool
     */
    public static function validateFileSize($fileSize, $maxSize = 10) {
        $maxBytes = $maxSize * 1024 * 1024; // Converter MB para bytes
        return $fileSize <= $maxBytes;
    }
}
