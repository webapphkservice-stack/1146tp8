/** @type {import('tailwindcss').Config} */
module.exports = {
  // 扫描所有后台视图（类以字面量写在 php 模板属性中，可被 JIT 扫描到）
  content: [
    './app/store/view/**/*.php',
    './app/store/view/**/*.html',
  ],
  theme: {
    extend: {},
  },
  // 模板中以字符串拼接生成的类（如 badge-success / badge-warning）无法被 content 扫描到，
  // 用 safelist 强制保留
  safelist: [
    'badge-success',
    'badge-warning',
  ],
  // 全站视图已迁移至 Tailwind，移除 amazeui.min.css 后开启 preflight（全局重置）
  corePlugins: {},
  plugins: [],
};
