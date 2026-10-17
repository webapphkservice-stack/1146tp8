/**
 * 轻量模态框垫片（替代已从项目移除的 amazeui modal 组件）
 *
 * 背景：amazeui.min.js / amazeui.min.css 已从项目删除，部分页面仍使用
 *       $('#x').modal({ onConfirm / onCancel }) 与 data-am-modal-confirm / data-am-modal-cancel，
 *       直接调用会因 $.fn.modal 不存在而抛 TypeError。本垫片用 jQuery + Tailwind 类复刻该 API，
 *       页面 JS 无需改动。
 *
 * DOM 约定（迁移脚本产出）：
 *   <div id="x" class="fixed inset-0 z-50 flex items-center justify-center hidden">
 *       <div class="relative bg-white rounded-lg shadow-lg">…</div>
 *       <span data-am-modal-cancel>取消</span>
 *       <span data-am-modal-confirm>确定</span>
 *   </div>
 * 遮罩层由垫片自动插入（[data-modal-mask]）。
 *
 * 用法：
 *   $('#x').modal({ onConfirm: function (e) { e.data /* 输入框值 *\/ } });
 *   $('#x').modal('open' | 'close');
 */
(function ($) {
    if (!$) {
        return;
    }

    /**
     * 取模态框内的输入值（prompt 场景：textarea 优先，其次 input）
     * @param {jQuery} $m
     * @return {string}
     */
    function value($m) {
        var $field = $m.find('textarea').first();
        if (!$field.length) {
            $field = $m.find('input').first();
        }
        return $field.length ? String($field.val() || '') : '';
    }

    function ensureMask($m) {
        var $mask = $m.children('[data-modal-mask]');
        if (!$mask.length) {
            $mask = $('<div class="absolute inset-0 bg-black/40" data-modal-mask></div>');
            $m.prepend($mask);
        }
        return $mask;
    }

    $.fn.modal = function (options) {
        var $m = this;

        if (typeof options === 'string') {
            if (options === 'open') {
                ensureMask($m);
                $m.removeClass('hidden');
            } else if (options === 'close') {
                $m.addClass('hidden');
            } else if (options === 'toggle') {
                $m.toggleClass('hidden');
            }
            return this;
        }

        options = options || {};
        ensureMask($m);
        $m.removeClass('hidden');

        // 先解绑，避免重复调用 modal() 时事件叠加
        $m.off('.amzModal');
        $m.on('click.amzModal', '[data-am-modal-cancel]', function () {
            $m.addClass('hidden');
            if (typeof options.onCancel === 'function') {
                options.onCancel.call(this, { data: value($m) });
            }
        });
        $m.on('click.amzModal', '[data-am-modal-confirm]', function () {
            $m.addClass('hidden');
            if (typeof options.onConfirm === 'function') {
                options.onConfirm.call(this, { data: value($m) });
            }
        });
        $m.on('click.amzModal', '[data-modal-mask]', function () {
            $m.addClass('hidden');
        });

        return this;
    };
})(window.jQuery);
