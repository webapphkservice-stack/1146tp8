/**
 * 马来西亚州属-城市数据
 * 有城市列表的州显示选择器，没有的州保持手动输入
 */
const cityMap = {
  '吉隆坡': [
    '甲洞 (Kepong)',
    '巴都 (Batu)',
    '冼都 (Sentul)',
    '文良港 (Setapak)',
    '旺沙玛珠 (Wangsa Maju)',
    '孟加拉拉 (Manjalara)',
    '满家乐 (Mont Kiara)',
    '白沙罗高原 (Bukit Damansara)',
    '士马勒 (Semarak)',
    '斯迪亚旺沙 (Setiawangsa)',
    '大使 (Duta)',
    '甘榜巴鲁 (Kampung Baru)',
    '拿督克拉末 (Datuk Keramat)',
    '班登 (Pandan)',
    '市中心/KLCC (Ibu Kota)',
    '十五碑 (Brickfields)',
    '沙叻 (Salak)',
    '班底谷 (Lembah Pantai)',
    '孟沙 (Bangsar)',
    '郊外岭 (Taman Desa)',
    '士布爹 (Seputeh)',
    '大城堡 (Sri Petaling)',
    '半山芭 (Pudu)',
    '蕉赖 (Cheras)',
    '康乐 (Connaught)',
    '武吉加里尔 (Bukit Jalil)',
    '敦拉扎克镇 (Bandar Tun Razak)',
    '新街场 (Sungai Besi)',
    '阿南达迈 (Alam Damai)'
  ]
};

/**
 * 获取指定州属的城市列表（无数据返回空数组）
 */
function getCityList(state) {
  return cityMap[state] || [];
}

module.exports = {
  cityMap: cityMap,
  getCityList: getCityList
};
