<?php
namespace app\admin\controller\setting;

// 注意：本控制器类名为 Cache，必须给 facade 起别名，否则与类声明冲突
use think\facade\Cache as CacheFacade;
use app\common\controller\Controller;

/**
 * 清理缓存（移植自 php admin/controller/setting/Cache.php，改造成不掉线版本）
 *
 * 纯 JSON 版：POST /admin/setting.cache/clear
 * 参数 item[]：
 *   temp    —— 清模板编译（runtime/temp、runtime/admin/temp）+ 日志（runtime/log、runtime/admin/log）+ 临时图片（runtime/image）
 *   opcache —— 清 PHP 字节码缓存（opcache_reset，CLI 下是空操作）
 *   data    —— 清框架 Cache（⚠ 高危！TP8 File 驱动以 runtime/cache 为目录，Cache::clear() 会 rmdir 整个目录，
 *                             登录态 token 也是存在这里 —— 所以 data 项单独清，用户自己承担掉线后果）
 * 不传 item 时默认 = temp + opcache（安全，不掉线）。
 *
 * 关于 token：TP8 File 驱动把 key 做了 md5 哈希当文件名，两级目录（runtime/cache/ab/xxxxxxxxxxxxxxxxxxxxxxxxxx.php），
 * 无法按 key 前缀精确过滤。所以"保留 token + 清 Cache"在 file 驱动下不可行。
 */
class Cache extends Controller
{
    public function clear()
    {
        $items = $this->request->param('item/a', ['temp', 'opcache']);
        if (!is_array($items) || empty($items)) {
            $items = ['temp', 'opcache'];
        }

        $runtime = runtime_path();
        $done = [];

        if (in_array('temp', $items, true)) {
            $this->deleteContents($runtime . 'temp');          // 框架模板编译
            $this->deleteContents($runtime . 'admin/temp');   // admin 应用编译
            $this->deleteContents($runtime . 'log');           // 框架日志
            $this->deleteContents($runtime . 'admin/log');     // admin 日志
            $this->deleteContents($runtime . 'image');         // 图片临时目录
            $done[] = '模板编译 & 日志 & 临时图片';
        }

        if (in_array('opcache', $items, true)) {
            if (function_exists('opcache_reset')) {
                @opcache_reset();
                $done[] = 'OPcache（字节码）';
            }
        }

        if (in_array('data', $items, true)) {
            // ⚠️ 会清掉登录 token，操作后需重新登录
            CacheFacade::clear();
            $done[] = '数据缓存（含登录态）';
        }

        if (empty($done)) {
            return $this->renderError('未选择任何可清理项');
        }

        return $this->renderSuccess(['cleared' => $done], '已清理：' . implode('、', $done));
    }

    /**
     * 清空目录内容（保留目录本身，跳过 .gitignore）
     * @param string $dir
     */
    private function deleteContents($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $handle = opendir($dir);
        if ($handle === false) {
            return;
        }
        while (false !== $file = readdir($handle)) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }
            $full = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($full)) {
                $this->deleteContents($full);
                @rmdir($full);
            } else {
                @unlink($full);
            }
        }
        closedir($handle);
    }
}
