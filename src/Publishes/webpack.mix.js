const path = require('path')
const rm = require('rimraf')
const mix = require('laravel-mix')
require('dotenv').config()

process.env.MIX_BASE_URL = process.env.MIX_BASE_URL.replace('${APP_NAME}', process.env.APP_NAME)
process.env.MIX_W7_URL = process.env.MIX_W7_URL.replace('${APP_NAME}', process.env.APP_NAME)

const assetsSubDirectory = 'assets'
const adminUrl = process.env.MIX_BASE_URL + '/admin'
const adminAssetsUrl = adminUrl + '/assets'

rm(path.resolve(__dirname, './public/admin'), function (error) {
    if (error) throw error
})

mix.webpackConfig({
    resolve: {
        alias: {
            'vue$': "vue/dist/vue.runtime.esm.js",
            '@':  path.resolve(__dirname, 'resources/admin')
        }
    },
    output: {
        publicPath: adminUrl + '/' //生成动态导入的js文件用的路径
    }
})
.setPublicPath('public/admin') //和webpackconfig的publicpath不是一回事，这里是给下面资源用的
.setResourceRoot(assetsSubDirectory) //相对mix的publicpath
.override(webpackConfig => {
    webpackConfig.module.rules.forEach(rule => {
      
        if (rule.test.toString() === /(\.(png|jpe?g|gif|webp)$|^((?!font).)*\.svg$)/.toString()) {
            if (Array.isArray(rule.use)) {
                rule.use.forEach(ruleUse => {
                    if (ruleUse.loader === 'file-loader') {
                        ruleUse.options.name = 'images/[name].[hash:7].[ext]',
                        ruleUse.options.esModule = false
                        ruleUse.options.publicPath = adminAssetsUrl
                        ruleUse.options.outputPath = assetsSubDirectory //静态文件输出路径 相对于mix的publicpath
                    }
                })
            }
        } else if (rule.test.toString() === /(\.(woff2?|ttf|eot|otf)$|font.*\.svg$)/.toString()) {
            if (Array.isArray(rule.use)) {
                rule.use.forEach(ruleUse => {
                    if (ruleUse.loader === 'file-loader') {
                        ruleUse.options.name = 'fonts/[name].[hash:7].[ext]',
                        ruleUse.options.publicPath = adminAssetsUrl
                        ruleUse.options.outputPath = assetsSubDirectory //静态文件输出路径 相对于mix的publicpath
                    }
                })
            }
        } else if (rule.test.toString() === /\.scss$/.toString()) {
            rule.oneOf.forEach(obj => {
                obj.use.forEach(loader => {
                    if (loader.loader === 'postcss-loader') {
                        // cssnano 导致的prod时有个报错，解决不了，所以删除该postcss的cssnano插件
                        delete loader.options.postcssOptions.plugins[1]
                        // loader.options.postcssOptions.plugins.splice(1, 1)
                        // console.log(loader.loader, loader.options.postcssOptions.plugins)
                    }
                })
            })
        }
    })
    // console.log(webpackConfig.module.rules)
    // process.exit()
})
.js('resources/admin/app.js', 'public/admin/app.js')
.version()
.vue()