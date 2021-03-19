<?php

namespace W7AddonCore\Foundation\Console;

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
        '.DS_Store',
        '/dist',
        '/core/resources/admin',
        '/core/app/Console',
        '/core/routes/console.php',
        '/core/vendor/illuminate/contracts/Consle',
        '/core/vendor/illuminate/console',
        '/core/vendor/symfony/console',
        '/core/vendor/illuminate/contracts/Auth',
        '/core/vendor/illuminate/auth',
        '/core/vendor/illuminate/contracts/Mail',
        '/core/vendor/illuminate/mail',
        '/core/repositories',
        '/core/vendor/colakiller/src/Foundation/Console',
        '/core/vendor/egulias/email-validator',
        '/core/vendor/illuminate/database/PostgresConnection.php',
        '/core/vendor/illuminate/database/SQLiteConnection.php',
        '/core/vendor/illuminate/database/SqlServerConnection.php',
        '/core/vendor/illuminate/database/PostgresConnection.php',
        '/core/vendor/illuminate/database/Console',
        '/core/vendor/illuminate/database/Connectors/PostgresConnector.php',
        '/core/vendor/illuminate/database/Connectors/SQLiteConnector.php',
        '/core/vendor/illuminate/database/Connectors/SqlServerConnector.php',
        '/core/vendor/illuminate/database/PDO/PostgresDriver.php',
        '/core/vendor/illuminate/database/PDO/SQLiteDriver.php',
        '/core/vendor/illuminate/database/PDO/SqlServerDriver.php',
        '/core/vendor/illuminate/database/Query/Grammars/PostgresGrammar.php',
        '/core/vendor/illuminate/database/Query/Grammars/SQLiteGrammar.php',
        '/core/vendor/illuminate/database/Query/Grammars/SqlServerGrammar.php',
        '/core/vendor/illuminate/database/Query/Processors/PostgresProcessor.php',
        '/core/vendor/illuminate/database/Query/Processors/SQLiteProcessor.php',
        '/core/vendor/illuminate/database/Query/Processors/SqlServerProcessor.php',
        '/core/vendor/illuminate/database/Schema/Grammars/PostgresGrammar.php',
        '/core/vendor/illuminate/database/Schema/Grammars/SQLiteGrammar.php',
        '/core/vendor/illuminate/database/Schema/Grammars/SqlServerGrammar.php',
        '/core/vendor/illuminate/database/Schema/PostgresBuilder.php',
        '/core/vendor/illuminate/database/Schema/PostgresSchemaState.php',
        '/core/vendor/illuminate/database/Schema/SQLiteBuilder.php',
        '/core/vendor/illuminate/database/Schema/SqLiteSchemaState.php',
        '/core/vendor/illuminate/database/Schema/SqlServerBuilder.php',
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
