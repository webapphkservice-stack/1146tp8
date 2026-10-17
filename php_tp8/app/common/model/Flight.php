<?php

namespace app\common\model;

use think\facade\Db;

/**
 * 航班搜索缓存 / 后台默认航班（轻量 Db 实现，移植自 php common/model/Flight）
 * Class Flight
 * @package app\common\model
 */
class Flight extends BaseModel
{
    protected $name = 'flight';

    /**
     * 匹配航班：后台默认(source=1) 优先，其次未过期接口缓存(source=0)
     * @param array $search origin,destination,departure_date,return_date,trip_type,cabin,currency,adult
     * @return array|null
     */
    public static function getMatch($search)
    {
        $query = Db::name('flight')->where('wxapp_id', self::getWxappId())
            ->where('is_delete', 0)
            ->where('origin', $search['origin'])
            ->where('destination', $search['destination'])
            ->where('departure_date', $search['departure_date'])
            ->where('trip_type', $search['trip_type'])
            ->where('cabin', $search['cabin'])
            ->where('currency', $search['currency'])
            ->where('adult', $search['adult']);
        if (!empty($search['return_date'])) {
            $query->where('return_date', $search['return_date']);
        } else {
            $query->whereNull('return_date');
        }
        $row = $query->order(['source' => 'desc', 'sort' => 'asc', 'flight_id' => 'desc'])
            ->find();
        if (!$row) {
            return null;
        }
        // 接口缓存过期判断
        if ($row['source'] == 0 && $row['expire_time'] > 0 && $row['expire_time'] < time()) {
            return null;
        }
        return $row;
    }

    /**
     * 写入接口缓存
     * @param array $params
     * @param array $flights 解析后的航班数组
     * @param int $expireHours 缓存小时数
     * @return bool
     */
    public static function saveCache($params, $flights, $expireHours = 168)
    {
        return (bool)Db::name('flight')->insert([
            'wxapp_id'       => self::getWxappId(),
            'source'         => 0,
            'title'          => '',
            'origin'         => strtoupper($params['origin']),
            'destination'    => strtoupper($params['destination']),
            'departure_date' => $params['departure_date'],
            'return_date'    => !empty($params['return_date']) ? $params['return_date'] : null,
            'trip_type'      => (int)$params['trip_type'],
            'cabin'          => (int)$params['cabin'],
            'currency'       => $params['currency'],
            'adult'          => (int)$params['adult'],
            'data'           => json_encode($flights, JSON_UNESCAPED_UNICODE),
            'hits'           => 0,
            'sort'           => 0,
            'expire_time'    => time() + (int)$expireHours * 3600,
            'is_delete'      => 0,
            'create_time'    => time(),
            'update_time'    => time(),
        ]);
    }

    /**
     * 排序最靠前的一条后台默认航班
     * @return array|null
     */
    public static function getDefaultRow()
    {
        $row = Db::name('flight')->where('wxapp_id', self::getWxappId())
            ->where('is_delete', 0)
            ->where('source', 1)
            ->order(['sort' => 'asc', 'flight_id' => 'desc'])
            ->find();
        return $row ?: null;
    }

    /**
     * 命中计数
     */
    public static function incHits($flightId)
    {
        Db::name('flight')->where('flight_id', $flightId)->inc('hits')->update();
    }

    /**
     * data 字段规范为航班数组（可能为 JSON 字符串或已解码数组）
     */
    public static function normalizeFlights($data)
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            return is_array($decoded) ? $decoded : [];
        }
        return is_array($data) ? $data : [];
    }

    /**
     * 后台默认航班列表（source=1）
     * @param array $params
     * @return \think\Paginator
     */
    public function getList($params = [])
    {
        $this->where('is_delete', '=', 0)
            ->where('source', '=', 1);
        if (!empty($params['origin'])) {
            $this->where('origin', 'like', "%{$params['origin']}%");
        }
        if (!empty($params['destination'])) {
            $this->where('destination', 'like', "%{$params['destination']}%");
        }
        return $this->order(['sort' => 'asc', 'flight_id' => 'desc'])
            ->paginate(15, false, ['query' => request()->param()]);
    }

    /**
     * 详情
     * @param $flightId
     * @return static|null
     */
    public static function detail($flightId)
    {
        return self::find($flightId);
    }

    /**
     * 新增（后台默认航班）
     * @param array $data
     * @return bool
     */
    public function add($data)
    {
        $data['source'] = 1;
        $data['hits'] = 0;
        $data['expire_time'] = 0;
        $data['create_time'] = time();
        $data['update_time'] = time();
        return $this->allowField(array_keys($data))->save($data);
    }

    /**
     * 编辑
     * @param array $data
     * @return bool
     */
    public function edit($data)
    {
        $data['update_time'] = time();
        return $this->allowField(array_keys($data))->save($data) !== false;
    }

    /**
     * 软删除
     * @return bool
     */
    public function setDelete()
    {
        return $this->allowField(['is_delete', 'update_time'])
            ->save(['is_delete' => 1, 'update_time' => time()]) !== false;
    }
}
