<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
// 注意：本类名为 Cache，直接 use think\facade\Cache 会与类名冲突
// （PHP 报 Cannot redeclare class ... previously declared as local import），必须取别名
use think\facade\Cache as CacheFacade;

/**
 * 清理缓存
 * 菜单入口：setting.cache/clear
 *
 * 移植自 php/source/application/store/controller/setting/Cache.php，针对 php_tp8 调整：
 *  1. think\Cache -> think\facade\Cache（TP8 门面）。
 *  2. $this->store['wxapp']['wxapp_id'] -> self::getWxappId()（TP8 后台登录态取法）。
 *  3. WEB_PATH / RUNTIME_PATH 常量在 TP8 不存在，改用 app()->getRootPath()/getRuntimePath()。
 *  4. DS 常量替换为 '/';删除临时文件时加了 is_file/is_dir 保护，避免误删。
 */
class Cache extends Controller
{
    /**
     * 清理缓存
     * @return mixed
     */
    public function clear()
    {
        if ($this->request->isAjax()) {
            $data = $this->postData('cache');
            $this->rmCache($data['keys'] ?? []);
            return $this->renderSuccess('操作成功');
        }
        return $this->fetch('clear', [
            'cacheList' => $this->getItems(),
        ]);
    }

    /**
     * 数据缓存项目
     * @return array
     */
    private function getItems()
    {
        $wxappId = self::getWxappId();
        return [
            'category' => [
                'type' => 'cache',
                'key'  => 'category_' . $wxappId,
                'name' => '商品分类',
            ],
            'setting'  => [
                'type' => 'cache',
                'key'  => 'setting_' . $wxappId,
                'name' => '商城设置',
            ],
            'wxapp'    => [
                'type' => 'cache',
                'key'  => 'wxapp_' . $wxappId,
                'name' => '小程序设置',
            ],
            'dealer'   => [
                'type' => 'cache',
                'key'  => 'dealer_setting_' . $wxappId,
                'name' => '分销设置',
            ],
            'sharing'  => [
                'type' => 'cache',
                'key'  => 'sharing_setting_' . $wxappId,
                'name' => '拼团设置',
            ],
            'temp'     => [
                'type'    => 'file',
                'name'    => '临时图片',
                'dirPath' => [
                    'web'     => app()->getRootPath() . 'public/temp/' . $wxappId . '/',
                    'runtime' => app()->getRuntimePath() . 'image/' . $wxappId . '/',
                ],
            ],
        ];
    }

    /**
     * 删除缓存
     * @param array|string $keys
     */
    private function rmCache($keys)
    {
        $cacheList = $this->getItems();
        $keys = array_intersect(array_keys($cacheList), (array)$keys);
        foreach ($keys as $key) {
            $item = $cacheList[$key];
            if ($item['type'] === 'cache') {
                CacheFacade::has($item['key']) && CacheFacade::delete($item['key']);
            } elseif ($item['type'] === 'file') {
                $this->deltree($item['dirPath']);
            }
        }
    }

    /**
     * 删除目录下所有文件
     * @param array|string $dirPath
     * @return bool
     */
    private function deltree($dirPath)
    {
        if (is_array($dirPath)) {
            foreach ($dirPath as $path) {
                $this->deleteFolder($path);
            }
            return true;
        }
        return $this->deleteFolder($dirPath);
    }

    /**
     * 递归删除指定目录下所有文件
     * @param string $path
     * @return bool
     */
    private function deleteFolder($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        foreach (scandir($path) as $val) {
            if (in_array($val, ['.', '..'], true)) {
                continue;
            }
            $full = $path . $val;
            if (is_dir($full . '/')) {
                $this->deleteFolder($full . '/');
                @rmdir($full);
            } elseif (is_file($full)) {
                @unlink($full);
            }
        }
        return true;
    }
}
