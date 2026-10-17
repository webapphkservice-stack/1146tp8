<?php
namespace app\admin\controller\setting;

use app\common\controller\Controller;

/**
 * 环境检测（移植自 php admin/controller/setting/Science.php）
 *
 * 纯 JSON：GET /admin/setting.science/index
 * 返回三段检测结果，每段为 [{name, value, status, remark}]，
 * status 取值 normal / warning / danger。
 */
class Science extends Controller
{
    public function index()
    {
        return $this->renderSuccess([
            'phpinfo'   => $this->phpinfo(),
            'server'    => $this->server(),
            'writeable' => $this->writeable(),
        ], 'ok');
    }

    /**
     * PHP 环境要求
     */
    private function phpinfo()
    {
        $list = [];
        // PHP 版本
        $list[] = [
            'name'   => 'php_version',
            'value'  => PHP_VERSION,
            'status' => version_compare(PHP_VERSION, '5.4.0', '>=') ? 'normal' : 'danger',
            'remark' => '要求 >= 5.4.0',
        ];
        // 扩展检测
        $extensions = [
            'curl'      => 'CURL（HTTP 请求）',
            'openssl'   => 'OpenSSL（加密/签名）',
            'gd'        => 'GD（图片处理）',
            'bcmath'    => 'BCMath（高精度计算）',
            'mbstring'  => 'Mbstring（多字节字符串）',
            'SimpleXML' => 'SimpleXML（XML 解析）',
        ];
        foreach ($extensions as $ext => $remark) {
            $list[] = [
                'name'   => $ext,
                'value'  => extension_loaded($ext) ? '支持' : '不支持',
                'status' => extension_loaded($ext) ? 'normal' : 'danger',
                'remark' => $remark,
            ];
        }
        // PDO + pdo_mysql
        $pdoOk = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        $list[] = [
            'name'   => 'pdo',
            'value'  => $pdoOk ? '支持' : '不支持',
            'status' => $pdoOk ? 'normal' : 'danger',
            'remark' => 'PDO + pdo_mysql（数据库）',
        ];
        return $list;
    }

    /**
     * 服务器信息
     */
    private function server()
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        return [
            [
                'name'   => 'system',
                'value'  => PHP_OS,
                'status' => $isWindows ? 'warning' : 'normal',
                'remark' => '建议使用 Linux',
            ],
            [
                'name'   => 'webserver',
                'value'  => $_SERVER['SERVER_SOFTWARE'] ?? '未知',
                'status' => (PHP_SAPI === 'isapi') ? 'warning' : 'normal',
                'remark' => 'Web 服务器',
            ],
            [
                'name'   => 'php',
                'value'  => PHP_VERSION,
                'status' => 'normal',
                'remark' => 'PHP 版本',
            ],
            [
                'name'   => 'upload_max',
                'value'  => ini_get('upload_max_filesize'),
                'status' => 'normal',
                'remark' => '上传大小限制',
            ],
            [
                'name'   => 'web_path',
                'value'  => public_path(),
                'status' => 'normal',
                'remark' => '站点根目录',
            ],
        ];
    }

    /**
     * 目录可写性
     */
    private function writeable()
    {
        $root = root_path();
        $dirs = [
            'uploads'      => public_path() . 'uploads' . DIRECTORY_SEPARATOR,
            'runtime'      => runtime_path(),
            'runtime_temp' => runtime_path() . 'temp' . DIRECTORY_SEPARATOR,
        ];
        $list = [];
        foreach ($dirs as $name => $dir) {
            $list[] = [
                'name'   => $name,
                'value'  => $dir,
                'status' => $this->checkWriteable($dir) ? 'normal' : 'danger',
                'remark' => '需可写',
            ];
        }
        return $list;
    }

    /**
     * 实测目录是否可写（不存在时尝试创建）
     * @param string $dir
     * @return bool
     */
    private function checkWriteable($dir)
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir)) {
            return false;
        }
        $file = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '_test_write.txt';
        if (@file_put_contents($file, 'test') === false) {
            return false;
        }
        @unlink($file);
        return true;
    }
}
