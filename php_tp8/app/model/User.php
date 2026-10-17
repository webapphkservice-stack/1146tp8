<?php
namespace app\model;

use app\common\model\BaseModel;
use app\common\model\Setting;
use app\common\library\google\GoogleTokenVerifier;
use think\facade\Cache;

/**
 * 对应 TP5 的 api/model/User.php 的 loginByGoogle / detail / token。
 * POC 省略了：Referee 关系建立、微信/支付宝/手机号分支、with 关联等，仅保留 Google 登录主干。
 */
class User extends BaseModel
{
    protected $name = 'user';

    protected $token;

    public function loginByGoogle($data)
    {
        // 1. 读取后台 Google 开关
        $googleSetting = Setting::getItem('google');
        if (empty($googleSetting['values']['is_enable'])) {
            throw new \Exception('Google 登录未开启，请在后台开启');
        }
        $webClientId = $googleSetting['values']['web_client_id'] ?? config('google.web_client_id');

        // 2. 校验 Google id_token（POC 允许通过静态属性覆盖 JWKS 地址）
        $payload = GoogleTokenVerifier::verify($data['id_token'], $webClientId);
        $googleId = $payload->sub;

        // 3. 已注册用户直接返回；否则新建
        $user = self::detail(['google_id' => $googleId]);
        if (!$user) {
            $model = new static;
            $model->save([
                'wxapp_id'  => self::getWxappId(),
                'google_id' => $googleId,
                'mobile'    => !empty($data['mobile']) ? $data['mobile'] : '',
                'nickName'  => !empty($data['nickName']) ? $data['nickName'] : '',
                'avatarUrl' => !empty($data['avatarUrl']) ? $data['avatarUrl'] : '',
                'is_delete' => 0,
            ]);
            $user = $model;
        }

        // 4. 生成登录令牌（在控制器内调用 token() 方法取回字符串，避免跨类读 protected 属性走 __get）
        return $user;
    }

    public static function detail($where, $with = [])
    {
        $query = self::where($where)->where('is_delete', 0);
        if (!empty($with)) {
            $query->with($with);
        }
        return $query->find();
    }

    /**
     * 生成登录令牌（POC 简化：md5 随机串 + 缓存 user_id）
     */
    public function token($userId)
    {
        $key   = $this->getGuidV4();
        $token = md5($key . $userId . self::getWxappId());
        $ttl   = (int)config('app.token_expire', 86400);
        Cache::set('token_' . $token, $userId, $ttl);
        return $token;
    }

    protected function getGuidV4()
    {
        return md5(uniqid(self::getWxappId(), true) . microtime());
    }
}
