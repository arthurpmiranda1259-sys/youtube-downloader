<?php
class StoreProvisioner {
    
    public static function createInstance($slug, $sourcePath, $destRoot) {
        $targetPath = $destRoot . '/' . $slug;
        
        if (is_dir($targetPath)) {
            throw new Exception("Já existe uma loja com este endereço (slug).");
        }
        
        if (!is_dir($sourcePath)) {
            throw new Exception("Pasta fonte do sistema não encontrada: $sourcePath");
        }
        
        // 1. Copy Files
        self::recurseCopy($sourcePath, $targetPath);
        
        // 2. Reset Database (Delete existing DB so a new one is created)
        // Detect database file dynamically
        $possibleDbPaths = [
            $targetPath . '/data/neodelivery.db',
            $targetPath . '/config/dentista.db',
            $targetPath . '/data/revexa.db'
        ];
        
        foreach ($possibleDbPaths as $dbFile) {
            if (file_exists($dbFile)) {
                unlink($dbFile);
            }
        }
        
        // 3. Create data/config directories if not exists
        if (!is_dir($targetPath . '/data')) {
            mkdir($targetPath . '/data', 0755, true);
        }
        if (!is_dir($targetPath . '/config')) {
            mkdir($targetPath . '/config', 0755, true);
        }
        
        // 4. Create uploads directory empty
        self::cleanDirectory($targetPath . '/uploads');
        
        return $targetPath;
    }
    
    private static function recurseCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::recurseCopy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    private static function cleanDirectory($dir) {
        if (!is_dir($dir)) return;
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
