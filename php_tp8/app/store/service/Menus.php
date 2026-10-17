<?php
namespace app\store\service;

/**
 * 商家后台菜单业务（移植自 TP5 store/service/Menus.php）
 *
 * 菜单配置来自 app/store/config/menus.php（对应 TP5 的 application/store/extra/menus.php），
 * TP8 多应用会自动加载应用 config 目录下的配置文件，config('menus') 可直接读取。
 *
 * 说明：think\Config -> config() 助手；其余多级菜单权限过滤逻辑与 TP5 完全一致。
 */
class Menus
{
    /** @var self 单例 */
    public static $instance;

    /** @var Auth 商家后台权限 */
    private $auth;

    /**
     * 获取实例
     * @return Menus
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof Menus)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->auth = Auth::getInstance();
    }

    private function __clone()
    {
    }

    /**
     * 后台菜单配置
     * @param string $routeUri
     * @param string $group
     * @return array
     */
    public function getMenus($routeUri, $group)
    {
        $menus = config('menus') ?: [];
        $this->first($menus, $routeUri, $group);
        return $menus;
    }

    /**
     * 一级菜单
     */
    private function first(&$menus, $routeUri, $group)
    {
        foreach ($menus as $key => &$first) {
            $indexData = $this->getMenusIndexUrls($first, 1);
            $first['index'] = $this->getAuthUrl($indexData);
            if ($first['index'] === false) {
                unset($menus[$key]);
                continue;
            }
            $first['active'] = $key === $group;
            if (isset($first['submenu'])) {
                $this->second($first['submenu'], $routeUri);
            }
        }
        unset($first);
    }

    /**
     * 二级菜单
     */
    public function second(&$menus, $routeUri)
    {
        foreach ($menus as $key => &$second) {
            $indexData = $this->getMenusIndexUrls($second, 2);
            $second['index'] = $this->getAuthUrl($indexData);
            if ($second['index'] === false) {
                unset($menus[$key]);
                continue;
            }
            $secondUris = [];
            if (isset($second['submenu'])) {
                $this->third($second['submenu'], $routeUri, $secondUris);
            } else {
                if (isset($second['uris'])) {
                    $secondUris = array_merge($secondUris, $second['uris']);
                } else {
                    $secondUris[] = $second['index'];
                }
            }
            !isset($second['active']) && $second['active'] = in_array($routeUri, $secondUris);
        }
        unset($second);
        // 删除空数组
        $menus = array_filter($menus);
    }

    /**
     * 三级菜单
     */
    private function third(&$menus, $routeUri, &$secondUris)
    {
        foreach ($menus as $key => &$third) {
            $indexData = $this->getMenusIndexUrls($third, 3);
            $third['index'] = $this->getAuthUrl($indexData);
            if ($third['index'] === false) {
                unset($menus[$key]);
                continue;
            }
            $thirdUris = [];
            if (isset($third['uris'])) {
                $secondUris = array_merge($secondUris, $third['uris']);
                $thirdUris = array_merge($thirdUris, $third['uris']);
            } else {
                $secondUris[] = $third['index'];
                $thirdUris[] = $third['index'];
            }
            $third['active'] = in_array($routeUri, $thirdUris);
        }
        unset($third);
    }

    /**
     * 获取指定菜单下的所有索引 url
     */
    private function getMenusIndexUrls(&$menus, $level = 1)
    {
        if (!isset($menus['index']) && !isset($menus['submenu'])) {
            return null;
        }
        $data = [];
        if (isset($menus['index']) && !empty($menus['index'])) {
            $data[] = $menus['index'];
        }
        if (isset($menus['submenu']) && !empty($menus['submenu'])) {
            foreach ($menus['submenu'] as $submenu) {
                $submenuIndex = $this->getMenusIndexUrls($submenu, ++$level);
                !is_null($submenuIndex) && $data = array_merge($data, $submenuIndex);
            }
        }
        return array_unique($data);
    }

    /**
     * 取出通过权限验证的 url 作为 index
     * @param array|null $urls
     * @return string|false
     */
    private function getAuthUrl($urls)
    {
        if (empty($urls)) {
            return false;
        }
        foreach ($urls as $url) {
            if ($this->auth->checkPrivilege($url)) {
                return $url;
            }
        }
        return false;
    }
}
