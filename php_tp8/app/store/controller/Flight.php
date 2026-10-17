<?php

namespace app\store\controller;

use app\common\model\Flight as FlightModel;
use app\common\model\Setting;
use app\api\service\FlightSearch;

/**
 * 商户后台航班管理：默认航班 CRUD + Bright Data 接口配置
 * Class Flight
 * @package app\store\controller
 */
class Flight extends Controller
{
    /**
     * 默认航班列表
     */
    public function index()
    {
        $model = new FlightModel;
        $list = $model->getList($this->request->param());
        $cacheCount = (new FlightModel)->where('source', 0)->where('is_delete', 0)->count();
        return $this->fetch('index', [
            'list' => $list,
            'cache_count' => $cacheCount,
        ]);
    }

    /**
     * 新增默认航班
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('form', [
                'model' => null,
            ]);
        }
        $data = $this->filterPost($this->postData('flight'));
        $model = new FlightModel;
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('flight/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑默认航班
     * @param int $flight_id
     */
    public function edit($flight_id = null)
    {
        if (!$this->request->isAjax()) {
            $model = FlightModel::detail($flight_id);
            if (!$model) {
                return $this->renderError('记录不存在');
            }
            return $this->fetch('form', [
                'model' => $model,
            ]);
        }
        $data = $this->filterPost($this->postData('flight'));
        $model = FlightModel::detail($flight_id);
        if (!$model) {
            return $this->renderError('记录不存在');
        }
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', url('flight/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除默认航班
     * @param $flight_id
     */
    public function delete($flight_id)
    {
        $model = FlightModel::detail($flight_id);
        if (!$model) {
            return $this->renderError('记录不存在');
        }
        if ($model->setDelete()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError('删除失败');
    }

    /**
     * Bright Data 接口配置
     */
    public function setting()
    {
        if (!$this->request->isAjax()) {
            $values = Setting::getItem('brightdata', $this->getWxappId()) ?: [];
            return $this->fetch('setting', [
                'values' => $values,
            ]);
        }
        $data = $this->postData('brightdata');
        $setting = Setting::detail('brightdata') ?: new Setting;
        $setting->key = 'brightdata';
        $setting->describe = '航班搜索(Bright Data)配置';
        $setting->wxapp_id = $this->getWxappId();
        $setting->values = $data;
        $setting->update_time = time();
        if ($setting->save()) {
            return $this->renderSuccess('保存成功', url('flight/setting'));
        }
        return $this->renderError('保存失败');
    }

    /**
     * 拉取真实航班数据预览（供保存为默认航班使用）
     */
    public function preview()
    {
        $param = $this->request->param();
        $search = [
            'origin'         => strtoupper(trim($param['origin'] ?? '')),
            'destination'    => strtoupper(trim($param['destination'] ?? '')),
            'departure_date' => trim($param['date'] ?? ''),
            'return_date'    => !empty($param['return_date']) ? trim($param['return_date']) : '',
            'trip_type'      => !empty($param['return_date']) ? 2 : 1,
            'cabin'          => (int)($param['cabin'] ?? 1),
            'adult'          => (int)($param['adult'] ?? 1),
            'currency'       => trim($param['currency'] ?? 'CNY') ?: 'CNY',
        ];
        if (!$search['origin'] || !$search['destination'] || !$search['departure_date']) {
            return $this->renderError('请填写出发地、目的地与出发日期');
        }
        $service = new FlightSearch($this->getWxappId());
        if (!$service->isEnabled()) {
            return $this->renderError('接口未启用或未配置（请先到“航班接口配置”填写 Zone 并开启）');
        }
        try {
            $result = $service->search($search);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
        return $this->renderSuccess($result);
    }

    /**
     * 过滤并规范化 POST 数据
     */
    private function filterPost($data)
    {
        if (isset($data['data'])) {
            $json = is_array($data['data']) ? json_encode($data['data'], JSON_UNESCAPED_UNICODE) : $data['data'];
            $decoded = json_decode($json, true);
            $data['data'] = is_array($decoded) ? $decoded : [];
        } else {
            $data['data'] = [];
        }
        $data['origin'] = strtoupper(trim($data['origin'] ?? ''));
        $data['destination'] = strtoupper(trim($data['destination'] ?? ''));
        $data['departure_date'] = trim($data['departure_date'] ?? '');
        $data['return_date'] = !empty($data['return_date']) ? trim($data['return_date']) : null;
        $data['title'] = trim($data['title'] ?? '');
        $data['cabin'] = (int)($data['cabin'] ?? 1);
        $data['adult'] = (int)($data['adult'] ?? 1);
        $data['trip_type'] = $data['return_date'] ? 2 : 1;
        $data['currency'] = trim($data['currency'] ?? 'CNY') ?: 'CNY';
        $data['sort'] = (int)($data['sort'] ?? 0);
        return $data;
    }
}
