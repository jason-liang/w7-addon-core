<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'package all php files and frontend files into dist dir';

    protected $modulePath;

    protected $distPath;

    protected $realExcludes = [];
    
    // 所有名称小写
    protected $excludes =  [
        '.git',
        '.github',
        '.gitignore',
        '.gitattributes',
        'composer.json',
        'composer.lock',
        '.editorconfig',
        'artisan',
        '*.md',
        '*.yml',
        '*.rst',
        '*.sh',
        'license',
        'license.txt',
        'notice',
        'makefile',
        'changes',
        '.editorconfig',
        '.env',
        '.env.example',
        '.DS_Store',
        'phpunit.xml',
        '/dist',
        '/core/database',
        '/core/config',
        '/core/routes',
        '/core/storage',
        '/core/tests',
        '/core/resources/admin',
        '/core/app/Console',
        '/core/routes/console.php',
        '/core/repositories',
        '/core/vendor/symfony/console',
        '/core/vendor/egulias/email-validator',
        '/core/vendor/colakiller/w7-addon-core/src/Publishes',
        // '/core/vendor/swiftmailer/swiftmailer',
        // '/core/vendor/fruitcake/laravel-cors',
        /* 
            iiluminate contracts 
        */
        '/core/vendor/laravel/framework/src/Illuminate/Contracts/Auth',
        '/core/vendor/laravel/framework/src/Illuminate/Contracts/Consle',
        '/core/vendor/laravel/framework/src/Illuminate/Contracts/Mail',
        '/core/vendor/laravel/framework/src/Illuminate/Contracts/Redis',
        /* 
            illuminate foundation
        */
        '/core/vendor/laravel/framework/src/Illuminate/Foundation/Auth',
        '/core/vendor/laravel/framework/src/Illuminate/Foundation/Console',
        '/core/vendor/laravel/framework/src/Illuminate/Foundation/Testing',
        /* 
            illuminate support
        */
        '/core/vendor/laravel/framework/src/Illuminate/Support/Testing',
        /* 
            illuminate package
        */
        '/core/vendor/laravel/framework/src/Illuminate/Auth',
        '/core/vendor/laravel/framework/src/Illuminate/Mail',
        [
            'dir' => '/core/vendor/laravel/framework/src/Illuminate/Testing',
            'excepts' => [
                'ParallelTestingServiceProvider.php',
                'Concerns'
            ]
        ],
        '/core/vendor/laravel/framework/src/Illuminate/Console',
        '/core/vendor/laravel/framework/src/Illuminate/Notification',
        '/core/vendor/laravel/framework/src/Illuminate/Redis',
        '/core/vendor/laravel/framework/src/Illuminate/database/PostgresConnection.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/SQLiteConnection.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/SqlServerConnection.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/PostgresConnection.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Console',
        '/core/vendor/laravel/framework/src/Illuminate/database/Connectors/PostgresConnector.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Connectors/SQLiteConnector.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Connectors/SqlServerConnector.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/PDO/PostgresDriver.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/PDO/SQLiteDriver.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/PDO/SqlServerDriver.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Query/Grammars/PostgresGrammar.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Query/Grammars/SQLiteGrammar.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Query/Grammars/SqlServerGrammar.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Query/Processors/PostgresProcessor.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Query/Processors/SQLiteProcessor.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Query/Processors/SqlServerProcessor.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/Grammars/PostgresGrammar.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/Grammars/SQLiteGrammar.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/Grammars/SqlServerGrammar.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/PostgresBuilder.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/PostgresSchemaState.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/SQLiteBuilder.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/SqLiteSchemaState.php',
        '/core/vendor/laravel/framework/src/Illuminate/database/Schema/SqlServerBuilder.php',
        [
            'dir' => '/core/vendor/nesbot/carbon/src/Carbon/Lang',
            'excepts' => [
                'en.php',
                'en_US.php',
                'zh.php',
                'zh_CN.php',
                'zh_Hans.php'
            ]
        ]
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->modulePath = module_path();

        $this->distPath = dist_path();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('build begining!');
        // empty dist dir
        is_dir($this->distPath) && rm_dirs($this->distPath);
        $this->buildFiles($this->modulePath);
        $this->info(PHP_EOL . 'build completed!');
    }

    protected function buildFiles($dir) {
        if (! is_dir($dir)) {
            $this->error('is not dir!');
            return;
        }

        $relativePath = $this->getRelativePath($dir);
        mkdir($this->distPath.DIRECTORY_SEPARATOR.$relativePath, 0777, true);

        $handle = opendir($dir);
        while($file = readdir($handle)) {
            if ($file == '.' || $file == '..')
                continue;
            
            $fullPath = $dir.DIRECTORY_SEPARATOR.$file;

            if ($this->isExclude($fullPath)) {
                $this->realExcludes[] = $relativePath.DIRECTORY_SEPARATOR.$file;
                continue;
            }

            echo ".";

            if (is_dir($fullPath)) {
                $this->buildFiles($fullPath); 
            } else {
                $pathInfo = pathinfo($fullPath);

                if (isset($pathInfo['extension']) && $pathInfo['extension'] === 'php') {
                    $content = file_get_contents($fullPath);

                    // 如何是blade模板
                    if (preg_match('/\.blade\.php$/', $pathInfo['basename'])) {
                        $content = substr_replace($content, "<?php defined('IN_IA') or exit('Access denied!'); ?>".PHP_EOL, 0, 0);
                    } else {
                        preg_match_all('/(<\?php\s+){1}(declare.*?;\s*)*(namespace.*?;\s*)*/i', $content, $matches);
                    
                        $content = preg_replace('/(<\?php\s+){1}(declare.*?;\s*)*(namespace.*?;\s*)*/i', 
                                                '${0}'." defined('IN_IA') or exit('Access denied!'); ", 
                                                strip_whitespace_and_comment($content), 1);
                    }

                    file_put_contents($this->distPath.DIRECTORY_SEPARATOR.$relativePath.DIRECTORY_SEPARATOR.$file, $content);
                } else {
                    copy($fullPath, $this->distPath.DIRECTORY_SEPARATOR.$relativePath.DIRECTORY_SEPARATOR.$file);
                }
            }
        }

        closedir($handle);
    }

    protected function getRelativePath($sPath) {

        return get_relative_path($this->modulePath, $sPath);
    }

    protected function isExclude($fullPath) {
        $pathInfo = pathinfo($fullPath);

        foreach ($this->excludes as $excludeFile) {
            if (is_array($excludeFile)) {
                $excepts = [];
               
                foreach ($excludeFile['excepts'] as $except) {
                    $excepts[] = strtolower($except);
                }

                if (strcasecmp($pathInfo['dirname'], $this->modulePath.$excludeFile['dir']) === 0
                    && isset($pathInfo['basename']) 
                    && !in_array(strtolower($pathInfo['basename']), $excepts)) {
                    return true;
                }
            } else {
                if (preg_match("/^\//", $excludeFile)) {

                    if (strcasecmp($fullPath, $this->modulePath.$excludeFile) === 0) {
                        return true;
                    }
                } else {
                    $excludeInfo = pathinfo($excludeFile);

                    if ($excludeInfo['filename'] === '*'
                            && isset($pathInfo['extension'])
                            && isset($excludeInfo['extension']) 
                            && strcasecmp($pathInfo['extension'], $excludeInfo['extension']) === 0) {
                        return true;
                    } else {
                        if (strcasecmp($pathInfo['basename'], $excludeFile) === 0) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }
}
