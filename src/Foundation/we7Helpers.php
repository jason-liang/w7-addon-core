<?php

if (! function_exists('module_name')) {
  function module_name()
  {
    return app()->getModuleName();
  }
}

if (! function_exists('module_path')) {
  function module_path($path = '')
  {
    return app()->modulePath($path);
  }
}

if (! function_exists('dist_path')) {
  function dist_path()
  {
    return app()->distPath();
  }
}

if (! function_exists('we7_path')) {
  function we7_path($path = '')
  {
    return app()->we7Path($path);
  }
}

if (! function_exists('we7_data_path')) {
  function we7_data_path($path = '')
  {
    return app()->we7DataPath($path);
  }
}

if (! function_exists('get_relative_path')) {
  function get_relative_path($parentPath, $sonPath)
  {
    $len = strlen($parentPath);
    $relativePath = substr($sonPath, $len + 1);
    
    return $relativePath;
  }
}

if (! function_exists('get_tagvalue_from_manifest')) {
  function get_tagvalue_from_manifest($filename, $tagname)
  {
    $dom = new DOMDocument();

    $dom->loadXML(file_get_contents($filename));
    
    return $dom->getElementsByTagName($tagname)->item(0)->textContent;
  }
}

if (! function_exists('mk_dirs')) {
  function mk_dirs($basePath = '', $dirs = [])
  {
    foreach ($dirs as $dirName => $subDirs) {
      $newDir = $basePath.DIRECTORY_SEPARATOR.$dirName;
      
      if (! is_dir($newDir)) {
        if (! mkdir($newDir, 0755, true)) {
          dd('Failed to create folder:'.$newDir);
        }
      }

      if (! empty($subDirs)) {
        mk_dirs($newDir, $subDirs);
      }
    }

    return true;
  }
}

if (! function_exists('rm_dirs')) {
  function rm_dirs($dir) {
    //先删除目录下的文件：
    $handle  = opendir($dir);

    while ($file = readdir($handle)) {
      if ($file == '.' || $file == '..')
        continue;
      
      $fullpath = $dir.DIRECTORY_SEPARATOR.$file;
      
      if(! is_dir($fullpath)) {
        unlink($fullpath);
      } else {
        rm_dirs($fullpath);
      }
    }
    
    closedir($handle);
    
    //删除当前文件夹：
    return rmdir($dir);
  }
}

if (! function_exists('strip_whitespace_and_comment')) {
  function strip_whitespace_and_comment($content) {
    $stripStr = '';
    
    //分析php源码
    $tokens =   token_get_all($content);
    
    $last_space = false;
    
    for ($i = 0, $j = count($tokens); $i < $j; $i++){
      if (is_string ($tokens[$i])){
        $last_space = false;
        $stripStr .= $tokens[$i];
      }
      else{
        switch ($tokens[$i][0]){
          //过滤各种PHP注释
          case T_COMMENT:
          case T_DOC_COMMENT:
            break;
          //过滤空格
          case T_WHITESPACE:
            if (!$last_space){
              $stripStr .= ' ';
              $last_space = true;
            }
            break;
          default:
            $last_space = false;
            $stripStr .= $tokens[$i][1];
        }
      }
    }

    return $stripStr;
  }
}

