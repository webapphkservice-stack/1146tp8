<?php

namespace app\store\controller;

use app\common\model\UserWaterTicket;
use app\common\model\UserWaterTicketLog;
use app\store\model\User as UserModel;

/**
 * 用户水票管理(送水)：查询用户当前水票、发放/扣减
 * Class WaterTicket
 * @package app\store\controller
 */
class WaterTicket extends Controller
{
    /**
     * 用户水票列表/发放
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 非 Ajax：列表查询（可能因本地 DB 缺表/列降级）
        if (!$this->request->isAjax()) {
            $search = trim((string)$this->request->get('search', ''));
            $list = new \think\paginator\driver\Bootstrap([], 15, 1, 0);
            $totalUser = 0;
            $totalNum = 0;
            try {
                $model = UserWaterTicket::alias('t')
                    ->join('user u', 'u.user_id = t.user_id', 'LEFT')
                    ->field('t.*, u.nickName');
                if ($search !== '') {
                    $model = $model->where(function ($query) use ($search) {
                        $query->where('t.user_id', '=', (int)$search)
                            ->whereOr('u.nickName', 'like', "%{$search}%");
                    });
                }
                $list = $model->order('t.update_time', 'desc')
                    ->paginate(15, false, ['query' => $this->request->get()]);
                $totalUser = UserWaterTicket::count();
                $totalNum  = (int)UserWaterTicket::sum('water_ticket_num');
            } catch (\Throwable $e) {
                \think\facade\Log::warning('WaterTicket::index 降级：' . $e->getMessage());
            }
            return $this->fetch('index', compact('list', 'search', 'totalUser', 'totalNum'));
        }
        // Ajax：发放/扣减水票
        if ($this->request->isAjax()) {
            $user_id = (int)$this->request->post('user_id');
            $num     = (int)$this->request->post('num');
            $remark  = trim((string)$this->request->post('remark', ''));
            if ($user_id <= 0) {
                return $this->renderError('请选择要发放水票的用户');
            }
            if ($num == 0) {
                return $this->renderError('发放/扣减数量不能为 0');
            }
            if (!UserModel::detail($user_id)) {
                return $this->renderError('用户不存在');
            }
            // 必须显式传 wxapp_id：水票按小程序归属，漏传会落到 wxapp_id=0 的脏记录(用户在C端查不到)
            $wxappId = (int)UserWaterTicket::$wxapp_id;
            $scene = $num > 0
                ? UserWaterTicketLog::SCENE_ADMIN_GRANT
                : UserWaterTicketLog::SCENE_ADMIN_DEDUCT;
            if (!UserWaterTicket::adjust($user_id, $num, $wxappId, $scene, $remark)) {
                return $this->renderError($num > 0 ? '发放失败，请重试' : '扣减失败（水票余额不足）');
            }
            return $this->renderSuccess(
                '操作成功，当前剩余水票 ' . UserWaterTicket::getBalance($user_id, $wxappId)
            );
        }
        // 关键字搜索：支持按用户ID或用户昵称查找
        $search = trim((string)$this->request->get('search', ''));
        $model = UserWaterTicket::alias('t')
            ->join('user u', 'u.user_id = t.user_id', 'LEFT')
            ->field('t.*, u.nickName');
        if ($search !== '') {
            $model = $model->where(function ($query) use ($search) {
                $query->where('t.user_id', '=', (int)$search)
                    ->whereOr('u.nickName', 'like', "%{$search}%");
            });
        }
        $list = $model->order('t.update_time', 'desc')
            ->paginate(15, false, ['query' => $this->request->get()]);
        // 统计：水票用户总数与票总数
        $totalUser = UserWaterTicket::count();
        $totalNum = (int)UserWaterTicket::sum('water_ticket_num');
        return $this->fetch('index', compact('list', 'search', 'totalUser', 'totalNum'));
    }
}