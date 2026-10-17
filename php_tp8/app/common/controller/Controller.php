<?php
namespace app\common\controller;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * TP8 多应用共享控制器基类。
 *
 * 移植要点：
 * 1. TP8 已移除 think\Controller 基类，这里自建一个最小基类（各应用控制器统一继承）。
 * 2. TP8 不会自动调用 initialize()，改为在构造函数里显式调用。
 * 3. $this->request 通过 request() 助手获取（TP5 用 Request::instance()）。
 * 4. TP8 已无 default_return_type 自动转 JSON，控制器必须显式 return json(...)。
 * 5. 已合并原单应用 app\BaseController 的 validate() 能力，便于迁移 TP5 控制器的 $this->validate() 调用。
 */
class Controller
{
    /** @var \think\Request */
    protected $request;

    /** @var \think\App */
    protected $app;

    /** @var bool 是否批量验证 */
    protected $batchValidate = false;

    /** @var array 控制器中间件 */
    protected $middleware = [];

    public function __construct()
    {
        $this->app     = app();
        $this->request = request();
        $this->initialize();
    }

    protected function initialize()
    {
    }

    protected function renderSuccess($data = [], $msg = 'success', $code = 1)
    {
        return json(compact('code', 'msg', 'data'));
    }

    protected function renderError($msg = 'error', $data = [], $code = 0)
    {
        return json(compact('code', 'msg', 'data'));
    }

    /**
     * 验证数据（合并自单应用 app\BaseController）
     * @param array        $data     数据
     * @param string|array $validate 验证器名或验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, string|array $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
}
