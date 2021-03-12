# we7_addon_framework core
框架微擎适配：
1. 移除.env文件，将全部配置直接写在config文件内 ✅
2. 移除所有的storage文件，或者将存放位置移未框架data目录里 ✅
3. 打包工具开发：
  - 创建目标文件夹dist ✅
  - 所有php文件头部加入"defined('WE7_ADDON_CORE_START') or exit('Access denied!');"，压缩后放入dist文件夹下 ✅
  - 编译前端文件（压缩，转换等）到dist文件夹
  - dist文件夹下去掉illuminate/console和illuminate/log相关包
4. 对foundation文件夹减重，不要的代码去掉，添加自己需要的库，然后发布自己的framework框架
  - 去掉migrations相关，移除除了mysql外的其他数据库驱动，只保留orm和database
  - 去掉某些库里一大堆的语言包文件，只保留中文和英文语言包
  - 去掉某些库里一大堆不知道什么的文件
  - 去掉session和cookie的包
  - 去掉auth包 
5. 添加对laravel-mix的支持

