<?php
namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\Spec as SpecModel;
use app\store\model\SpecValue as SpecValueModel;

/**
 * 商品规格控制器（服务端渲染版，移植自 TP5 store/controller/goods/Spec.php）
 */
class Spec extends Controller
{
    /**
     * 添加规则组
     * @param string $spec_name
     * @param string $spec_value
     */
    public function addSpec($spec_name, $spec_value)
    {
        if (!$specId = (new SpecModel)->getSpecIdByName($spec_name)) {
            $specId = (new SpecModel)->add($spec_name);
            if ($specId && $specValueId = (new SpecValueModel)->add($specId, $spec_value)) {
                return $this->renderSuccess('', '', [
                    'spec_id'       => (int)$specId,
                    'spec_value_id' => (int)$specValueId,
                ]);
            }
            return $this->renderError();
        }
        if ($specValueId = (new SpecValueModel)->getSpecValueIdByName($specId, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_id'       => (int)$specId,
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        if ($specValueId = (new SpecValueModel)->add($specId, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_id'       => (int)$specId,
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        return $this->renderError();
    }

    /**
     * 添加规格值
     * @param int $spec_id
     * @param string $spec_value
     */
    public function addSpecValue($spec_id, $spec_value)
    {
        if ($specValueId = (new SpecValueModel)->getSpecValueIdByName($spec_id, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        if ($specValueId = (new SpecValueModel)->add($spec_id, $spec_value)) {
            return $this->renderSuccess('', '', [
                'spec_value_id' => (int)$specValueId,
            ]);
        }
        return $this->renderError();
    }
}
