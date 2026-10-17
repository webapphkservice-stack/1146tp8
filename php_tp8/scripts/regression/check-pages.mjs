// 阶段B：后台旧 jQuery 插件逐页回归
// 用法：BASE_URL=http://127.0.0.1:8070 node scripts/regression/check-pages.mjs
// 依赖：playwright（npx playwright install chromium）
import { chromium } from 'playwright';

const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8070';

// 已经过路由探测、登录后可访问的后台页面（id-free / add / index）。
// 覆盖：fileLibrary(图片库) / selectData(选数据) / layer(全局) / diy(Vue+jQuery4) / uCheck(复选框)
// 说明：edit/copy_master/step1 等需要具体 id 或是私有方法，不在自动清单内（已确认是「需传参」设计，非缺口）。
const PAGES = [
  '/store/goods/add',
  '/store/goods/index',
  '/store/market.coupon/add',
  '/store/market.coupon/index',
  '/store/wxapp.page/add',
  '/store/wxapp.page/index',
  '/store/shop.clerk/add',
  '/store/shop.clerk/index',
  '/store/market.basic/full_free',
  '/store/apps.dealer.setting/index',
  '/store/apps.bargain.active/add',
  '/store/apps.bargain.active/index',
  '/store/apps.sharing.goods/add',
  '/store/apps.sharing.goods/index',
  '/store/apps.sharp.active/add',
  '/store/apps.sharp.active/index',
  '/store/apps.sharp.goods/add',
];

// 可能触发 fileLibrary / selectData 弹层的选择器（点击后验证弹层能打开、且无 JS 报错）
const TRIGGER_SELECTORS = [
  '.j-selectImg', '.j-selectImage', 'img.select-img', '.file-link',
  '.j-selectGoods', '.j-selectData', '.select-data', '[data-open="select-data"]',
  '.j-selectShop',
];

async function login(page) {
  await page.goto(BASE_URL + '/store/passport/login', { waitUntil: 'networkidle' });
  await page.fill('input[name="User[user_name]"]', 'admin');
  await page.fill('input[name="User[password]"]', '123456');
  await page.click('#btn-submit');
  // 登录成功后 layer.msg 1.5s 后跳转
  await page.waitForFunction(() => !location.href.includes('passport/login'), { timeout: 10000 })
    .catch(() => { throw new Error('登录失败或会话未建立（检查账号/数据库）'); });
}

async function checkGlobals(page) {
  return page.evaluate(() => {
    const jq = (window.jQuery && jQuery.fn) ? jQuery.fn.jquery : null;
    return {
      jquery: jq,
      fileLibrary: !!(window.jQuery && jQuery.fn.fileLibrary),
      selectData: !!(window.jQuery && jQuery.fn.selectData),
      layer: typeof window.layer,
      uCheck: !!(window.jQuery && jQuery.fn.uCheck),
    };
  });
}

async function tryTrigger(page) {
  for (const sel of TRIGGER_SELECTORS) {
    const el = await page.$(sel);
    if (!el) continue;
    try {
      await el.click({ timeout: 2000 });
      await page.waitForTimeout(800);
      const opened = await page.$('.layui-layer') !== null;
      // 关闭可能弹出的 layer
      await page.evaluate(() => { if (window.layer) layer.closeAll(); });
      await page.waitForTimeout(300);
      return { triggered: sel, opened };
    } catch (e) {
      return { triggered: sel, opened: false, error: String(e).slice(0, 120) };
    }
  }
  return { triggered: null, opened: false };
}

(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext();
  const page = await context.newPage();

  const consoleErrors = [];
  const pageErrors = [];
  page.on('console', (msg) => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });
  page.on('pageerror', (err) => { pageErrors.push(err.message); });

  console.log('== 登录 ==');
  await login(page);
  console.log('登录成功，开始逐页回归（BASE_URL=' + BASE_URL + '）\n');

  const results = [];
  for (const path of PAGES) {
    consoleErrors.length = 0;
    pageErrors.length = 0;
    let status = 'OK';
    let globals = {};
    let trigger = {};
    let httpStatus = null;
    try {
      const resp = await page.goto(BASE_URL + path, { waitUntil: 'networkidle', timeout: 20000 });
      httpStatus = resp ? resp.status() : null;
      globals = await checkGlobals(page);
      trigger = await tryTrigger(page);
    } catch (e) {
      status = 'ERR';
      pageErrors.push(String(e).slice(0, 200));
    }
    const jsErrors = pageErrors.length + consoleErrors.length;
    if (jsErrors > 0) status = 'JS_ERR';
    results.push({ path, httpStatus, status, globals, trigger, consoleErrors, pageErrors });

    console.log(
      `${status.padEnd(7)} ${String(httpStatus).padEnd(4)} ${path}` +
      `  jq=${globals.jquery || '-'} fileLib=${globals.fileLibrary ? 'Y' : 'n'}` +
      ` selectData=${globals.selectData ? 'Y' : 'n'} layer=${globals.layer}` +
      ` uCheck=${globals.uCheck ? 'Y' : 'n'}` +
      `  trigger=${trigger.triggered || '-'}(${trigger.opened ? 'opened' : 'no'})` +
      (jsErrors ? `  ERR=${jsErrors}` : '')
    );
    if (consoleErrors.length) console.log('   console: ' + consoleErrors.slice(0, 3).join(' | '));
    if (pageErrors.length) console.log('   pageerr: ' + pageErrors.slice(0, 3).join(' | '));
  }

  await browser.close();

  const failed = results.filter((r) => r.status !== 'OK');
  console.log(`\n== 汇总 ==  共 ${results.length} 页，异常 ${failed.length} 页`);
  if (failed.length) {
    console.log('异常页面：');
    failed.forEach((r) => console.log(`  ${r.status} ${r.path} ${r.consoleErrors.concat(r.pageErrors).slice(0, 2).join(' | ')}`));
    process.exit(1);
  }
  console.log('全部通过（仅前端 JS 维度；后端业务错误需另测）');
})();
