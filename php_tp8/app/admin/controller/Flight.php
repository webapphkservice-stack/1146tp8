<?php
namespace app\admin\controller;

use think\facade\Db;
use app\api\service\FlightSearch;
use app\common\controller\Controller;
use app\common\model\BaseModel;

/**
 * 航班管理（移植自 php admin/controller/Flight.php）
 *
 * 纯 JSON：
 *   GET  /admin/flight/index?origin=&destination=        默认航班列表（source=1）
 *   POST /admin/flight/add                               新增
 *   POST /admin/flight/edit?flight_id=1                  编辑
 *   POST /admin/flight/delete?flight_id=1                软删
 *   GET  /admin/flight/setting                           读 Bright Data 配置
 *   POST /admin/flight/setting                           写配置
 *   POST /admin/flight/fetch                             拉取真实航班数据（调试用）
 *
 * source 说明：1=后台维护的默认航班，0=接口抓取的缓存航班。
 */
class Flight extends Controller
{
    /** @var int 航班功能固定归属的小程序 */
    const WXAPP_ID = 10001;

    /** @var int 后台维护 */
    const SOURCE_ADMIN = 1;

    /** @var int 接口缓存 */
    const SOURCE_CACHE = 0;

    /**
     * 默认航班列表
     */
    public function index()
    {
        $query = Db::name('flight')
            ->where('source', self::SOURCE_ADMIN)
            ->where('is_delete', 0);

        $origin      = trim((string)$this->request->param('origin', ''));
        $destination = trim((string)$this->request->param('destination', ''));
        if ($origin !== '') {
            $query->where('origin', 'like', '%' . $origin . '%');
        }
        if ($destination !== '') {
            $query->where('destination', 'like', '%' . $destination . '%');
        }

        $list = $query->order('sort', 'asc')->order('flight_id', 'desc')->paginate(15);
        // 接口缓存命中数
        $cacheCount = Db::name('flight')
            ->where('source', self::SOURCE_CACHE)
            ->where('is_delete', 0)
            ->count();

        return $this->renderSuccess(['list' => $list, 'cache_count' => $cacheCount], 'ok');
    }

    /**
     * 新增
     */
    public function add()
    {
        $data = $this->filterPost();
        if (empty($data['origin']) || empty($data['destination'])) {
            return $this->renderError('出发地与目的地不能为空');
        }
        $data['source']      = self::SOURCE_ADMIN;
        $data['hits']        = 0;
        $data['expire_time'] = 0;
        $data['wxapp_id']    = self::WXAPP_ID;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['data']        = json_encode($data['data'], JSON_UNESCAPED_UNICODE);

        $flightId = Db::name('flight')->insertGetId($data);
        return $this->renderSuccess(['flight_id' => $flightId], '添加成功');
    }

    /**
     * 编辑
     */
    public function edit()
    {
        $flightId = (int)$this->request->param('flight_id', 0);
        if (!$this->findRow($flightId)) {
            return $this->renderError('记录不存在');
        }
        $data = $this->filterPost();
        $data['update_time'] = time();
        if (isset($data['data'])) {
            $data['data'] = json_encode($data['data'], JSON_UNESCAPED_UNICODE);
        }
        Db::name('flight')->where('flight_id', $flightId)->update($data);
        return $this->renderSuccess([], '更新成功');
    }

    /**
     * 软删除
     */
    public function delete()
    {
        $flightId = (int)$this->request->param('flight_id', 0);
        if (!$this->findRow($flightId)) {
            return $this->renderError('记录不存在');
        }
        Db::name('flight')->where('flight_id', $flightId)
            ->update(['is_delete' => 1, 'update_time' => time()]);
        return $this->renderSuccess([], '删除成功');
    }

    /**
     * Bright Data 航班搜索配置
     */
    public function setting()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param('brightdata/a', []);
            $this->saveSetting('brightdata', '航班搜索(Bright Data)配置', $data);
            return $this->renderSuccess(['values' => $data], '保存成功');
        }
        return $this->renderSuccess(['values' => $this->getSetting('brightdata')], 'ok');
    }

    /**
     * 拉取真实航班数据（调试）
     */
    public function fetch()
    {
        $params = [
            'origin'         => trim((string)$this->request->param('origin', '')),
            'destination'    => trim((string)$this->request->param('destination', '')),
            'departure_date' => trim((string)$this->request->param('date', '')),
            'return_date'    => trim((string)$this->request->param('return_date', '')),
            'cabin'          => (int)$this->request->param('cabin', 1),
            'adult'          => (int)$this->request->param('adult', 1),
            'currency'       => trim((string)$this->request->param('currency', 'CNY')),
        ];
        if ($params['origin'] === '' || $params['destination'] === '' || $params['departure_date'] === '') {
            return $this->renderError('出发地、目的地、出发日期不能为空');
        }
        $params['trip_type'] = $params['return_date'] !== '' ? 2 : 1;

        // FlightSearch 内部用 Setting::getItem()，依赖 BaseModel 的静态 wxapp_id
        BaseModel::setApiWxappId(self::WXAPP_ID);
        $search = new FlightSearch(self::WXAPP_ID);
        if (!$search->isEnabled()) {
            return $this->renderError('航班搜索未启用，请先配置 Bright Data（启用开关 + API Key + Zone）');
        }
        try {
            $result = $search->search($params);
        } catch (\Throwable $e) {
            return $this->renderError('拉取失败：' . $e->getMessage());
        }
        return $this->renderSuccess($result, 'ok');
    }

    /**
     * 规范化提交数据
     * @return array
     */
    private function filterPost()
    {
        $data = $this->request->param('flight/a', []);
        // data 字段统一为数组（存储时再 json_encode）
        if (isset($data['data']) && is_string($data['data'])) {
            $decoded = json_decode($data['data'], true);
            $data['data'] = is_array($decoded) ? $decoded : [];
        }
        foreach (['origin', 'destination', 'flight_no'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = strtoupper(trim((string)$data[$field]));
            }
        }
        foreach (['departure_date', 'return_date', 'title'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = trim((string)$data[$field]);
            }
        }
        if (isset($data['return_date']) && $data['return_date'] === '') {
            $data['return_date'] = null;
        }
        $data['cabin']     = isset($data['cabin']) ? (int)$data['cabin'] : 1;
        $data['adult']     = isset($data['adult']) ? (int)$data['adult'] : 1;
        $data['currency']  = !empty($data['currency']) ? $data['currency'] : 'CNY';
        $data['sort']      = isset($data['sort']) ? (int)$data['sort'] : 100;
        $data['trip_type'] = empty($data['return_date']) ? 1 : 2;
        return $data;
    }

    /**
     * @param int $flightId
     * @return array|null
     */
    private function findRow($flightId)
    {
        return Db::name('flight')
            ->where('flight_id', $flightId)
            ->where('is_delete', 0)
            ->find();
    }

    /**
     * 读取 setting 配置
     */
    private function getSetting($key)
    {
        $row = Db::name('setting')
            ->where('key', $key)
            ->where('wxapp_id', self::WXAPP_ID)
            ->find();
        $values = $row ? json_decode($row['values'], true) : [];
        return is_array($values) ? $values : [];
    }

    /**
     * 写入 setting 配置
     */
    private function saveSetting($key, $describe, $values)
    {
        $json = json_encode($values, JSON_UNESCAPED_UNICODE);
        $row  = Db::name('setting')->where('key', $key)->where('wxapp_id', self::WXAPP_ID)->find();
        if ($row) {
            Db::name('setting')->where('key', $key)->where('wxapp_id', self::WXAPP_ID)
                ->update(['values' => $json, 'update_time' => time()]);
        } else {
            Db::name('setting')->insert([
                'key'         => $key,
                'describe'    => $describe,
                'values'      => $json,
                'wxapp_id'    => self::WXAPP_ID,
                'update_time' => time(),
            ]);
        }
    }
}
