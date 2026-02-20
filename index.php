<?php
define('ROOT_PATH', __DIR__);

require_once ROOT_PATH . '/system/config/config.php';
require_once ROOT_PATH . '/system/config/database.php';

$coreClasses = [
    'Controller',
    'Database', 
    'Router',
    'App'
];

foreach ($coreClasses as $className) {
    $filePath = SYSTEM_PATH . '/core/' . $className . '.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    } else {
        die("Критическая ошибка: Не найден файл {$filePath}");
    }
}

try {
    $db = new Database([
        'host' => DB_HOST,
        'dbname' => DB_NAME,
        'username' => DB_USER,
        'password' => DB_PASS,
        'charset' => 'utf8mb4'
    ]);
} catch (Exception $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

spl_autoload_register(function ($class) use ($db) {
    if ($class === 'AchievementTriggers') {
        $file = ROOT_PATH . '/system/controllers/users/AchievementTriggers.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    $classPath = str_replace('\\', '/', $class);

    $basePaths = [
        ROOT_PATH . '/system/controllers',
        ROOT_PATH . '/system',
        ROOT_PATH . '/system/core',
        ROOT_PATH . '/system/helpers',
        ROOT_PATH . '/system/fields',
        ROOT_PATH . '/system/html_blocks',
        ROOT_PATH . '/system/post_blocks',
        ROOT_PATH . '/system/plugins'
    ];
    
    $helpersDir = ROOT_PATH . '/system/helpers';
    if (is_dir($helpersDir)) {
        $helperSubdirs = glob($helpersDir . '/*', GLOB_ONLYDIR);
        foreach ($helperSubdirs as $subdir) {
            $basePaths[] = $subdir;
        }
    }
    
    $controllersDir = ROOT_PATH . '/system/controllers';
    if (is_dir($controllersDir)) {
        $modules = glob($controllersDir . '/*', GLOB_ONLYDIR);
        foreach ($modules as $moduleDir) {
            $basePaths[] = $moduleDir;
            
            $modelsSubdir = $moduleDir . '/models';
            if (is_dir($modelsSubdir)) {
                $basePaths[] = $modelsSubdir;
            }
            
            if (is_dir($moduleDir . '/actions')) {
                $basePaths[] = $moduleDir . '/actions';
            }
        }
    }
    
    if (preg_match('/(.+?)Model$/', $class, $matches)) {
        $baseName = $matches[1];
        $classNameWithoutModel = str_replace('Model', '', $class);
        
        $possibleFiles = [
            $class . '.php',
            $classNameWithoutModel . 'Model.php',
            'Model.php'
        ];
        
        $controllerDirs = glob(ROOT_PATH . '/system/controllers/*', GLOB_ONLYDIR);
        
        foreach ($controllerDirs as $controllerDir) {
            foreach ($possibleFiles as $fileName) {
                $fullPath = $controllerDir . '/' . $fileName;
                if (file_exists($fullPath)) {
                    require_once $fullPath;
                    if (class_exists($class)) {
                        return;
                    }
                }
                
                $modelSubdirPath = $controllerDir . '/models/' . $fileName;
                if (file_exists($modelSubdirPath)) {
                    require_once $modelSubdirPath;
                    if (class_exists($class)) {
                        return;
                    }
                }
            }
        }

        $modelName = strtolower($baseName);
        $modelDir = ROOT_PATH . '/system/controllers/' . $modelName;
        
        if (is_dir($modelDir)) {
            foreach ($possibleFiles as $fileName) {
                $modelFile = $modelDir . '/' . $fileName;
                if (file_exists($modelFile)) {
                    require_once $modelFile;
                    if (class_exists($class)) {
                        return;
                    }
                }
                
                $modelSubdirFile = $modelDir . '/models/' . $fileName;
                if (file_exists($modelSubdirFile)) {
                    require_once $modelSubdirFile;
                    if (class_exists($class)) {
                        return;
                    }
                }
            }
        }
    }

    $possibleFiles = [
        $classPath . '.php',
        $class . '.php',
        basename($classPath) . '.php',
    ];
    
    foreach ($basePaths as $basePath) {
        foreach ($possibleFiles as $file) {
            $fullPath = $basePath . '/' . $file;
            if (file_exists($fullPath)) {
                require_once $fullPath;
                return;
            }
        }
    }
});

if (isset($_SESSION['user_id'])) {
    require_once SYSTEM_PATH . '/core/UserActivityManager.php';
    $activityManager = UserActivityManager::getInstance($db);
    if ($activityManager) {
        $activityManager->touch($_SESSION['user_id']);
    }
}

function loadAllHelpers($dir) {
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $fullPath = $dir . '/' . $file;
        
        if (is_dir($fullPath)) {
            loadAllHelpers($fullPath);
        } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            require_once $fullPath;
        }
    }
}

$helpersPath = ROOT_PATH . '/system/helpers';
if (is_dir($helpersPath)) {
    loadAllHelpers($helpersPath);
}

define('CONTROLLERS_PATH', ROOT_PATH . '/system/controllers');
$permissionsFiles = glob(CONTROLLERS_PATH . '/*/permissions.php');
foreach ($permissionsFiles as $file) {
    if (file_exists($file) && is_readable($file)) {
        require_once $file;
    }
}

if (class_exists('AssetManager')) {
    AssetManager::getInstance()->clear();
}

try {
    $app = new App();
    DatabaseRegistry::init($db);
    $app->run();
} catch (Exception $e) {
    if (defined('DEBUG') && DEBUG === true) {
        echo '<h1>Error</h1>';
        echo '<p>' . $e->getMessage() . '</p>';
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    } else {
        header("HTTP/1.0 500 Internal Server Error");
        require ROOT_PATH . '/templates/' . DEFAULT_TEMPLATE . '/500.php';
    }
}