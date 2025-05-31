/**
 * 字体库管理器
 * 更新的字体库管理器
 * 基于整理后的字体目录结构生成，只包含可用字体
 */

class FontLibraryManager {
  constructor() {
    this.fontList = [];
    this.basePath = '/fonts/organized_fonts/';
  }

  /**
   * 初始化字体库
   */
  async initFontLibrary() {
    // 只包含可用的字体映射配置（基于font_summary.txt报告）
    const fontMappings = [
{
      "name": "思源黑体 SC",
      "displayName": "思源黑体 SC",
      "files": [
            "SourceHanSansSC-VF.ttf"
      ],
      "category": "chinese",
      "weights": [
            100,
            200,
            300,
            400,
            500,
            600,
            700,
            800,
            900
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/Source_Han_Sans_SC"
},
{
      "name": "思源宋体 SC",
      "displayName": "思源宋体 SC",
      "files": [
            "SourceHanSerifSC-VF.ttf"
      ],
      "category": "chinese",
      "weights": [
            100,
            200,
            300,
            400,
            500,
            600,
            700,
            800,
            900
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/Source_Han_Serif_SC"
},
{
      "name": "阿里巴巴普惠体",
      "displayName": "阿里巴巴普惠体",
      "files": [
            "AlibabaPuHuiTi-3-35-Thin.woff",
            "AlibabaPuHuiTi-3-35-Thin.ttf",
            "AlibabaPuHuiTi-3-35-Thin.otf",
            "AlibabaPuHuiTi-3-35-Thin.woff2",
            "AlibabaPuHuiTi-3-55-Regular.woff",
            "AlibabaPuHuiTi-3-55-Regular.ttf",
            "AlibabaPuHuiTi-3-55-Regular.otf",
            "AlibabaPuHuiTi-3-55-Regular.woff2",
            "AlibabaPuHuiTi-3-65-Medium.woff2",
            "AlibabaPuHuiTi-3-65-Medium.ttf",
            "AlibabaPuHuiTi-3-65-Medium.otf",
            "AlibabaPuHuiTi-3-65-Medium.woff",
            "AlibabaPuHuiTi-3-115-Black.ttf",
            "AlibabaPuHuiTi-3-115-Black.otf",
            "AlibabaPuHuiTi-3-115-Black.woff2",
            "AlibabaPuHuiTi-3-115-Black.woff",
            "AlibabaPuHuiTi-3-45-Light.otf",
            "AlibabaPuHuiTi-3-45-Light.woff",
            "AlibabaPuHuiTi-3-45-Light.ttf",
            "AlibabaPuHuiTi-3-45-Light.woff2",
            "AlibabaPuHuiTi-3-105-Heavy.ttf",
            "AlibabaPuHuiTi-3-105-Heavy.otf",
            "AlibabaPuHuiTi-3-105-Heavy.woff2",
            "AlibabaPuHuiTi-3-105-Heavy.woff",
            "AlibabaPuHuiTi-3-85-Bold.woff2",
            "AlibabaPuHuiTi-3-85-Bold.woff",
            "AlibabaPuHuiTi-3-85-Bold.ttf",
            "AlibabaPuHuiTi-3-85-Bold.otf",
            "AlibabaPuHuiTi-3-95-ExtraBold.ttf",
            "AlibabaPuHuiTi-3-95-ExtraBold.otf",
            "AlibabaPuHuiTi-3-95-ExtraBold.woff2",
            "AlibabaPuHuiTi-3-95-ExtraBold.woff",
            "AlibabaPuHuiTi-3-55-RegularL3.ttf",
            "AlibabaPuHuiTi-3-55-RegularL3.otf",
            "AlibabaPuHuiTi-3-55-RegularL3.woff",
            "AlibabaPuHuiTi-3-55-RegularL3.woff2",
            "AlibabaPuHuiTi-3-75-SemiBold.otf",
            "AlibabaPuHuiTi-3-75-SemiBold.ttf",
            "AlibabaPuHuiTi-3-75-SemiBold.woff",
            "AlibabaPuHuiTi-3-75-SemiBold.woff2"
      ],
      "category": "chinese",
      "weights": [
            100,
            200,
            300,
            400,
            500,
            600,
            700,
            800,
            900
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/AlibabaPuHuiTi"
},
{
      "name": "刘建毛草",
      "displayName": "刘建毛草",
      "files": [
            "LiuJianMaoCao-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/Liu_Jian_Mao_Cao"
},
{
      "name": "龙藏体",
      "displayName": "龙藏体",
      "files": [
            "LongCang-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/Long_Cang"
},
{
      "name": "马善政楷书",
      "displayName": "马善政楷书",
      "files": [
            "MaShanZheng-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/Ma_Shan_Zheng"
},
{
      "name": "站酷快乐体",
      "displayName": "站酷快乐体",
      "files": [
            "ZCOOLKuaiLe-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/ZCOOL_KuaiLe"
},
{
      "name": "站酷庆科黄油体",
      "displayName": "站酷庆科黄油体",
      "files": [
            "ZCOOLQingKeHuangYou-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/ZCOOL_QingKe_HuangYou"
},
{
      "name": "站酷小薇",
      "displayName": "站酷小薇",
      "files": [
            "ZCOOLXiaoWei-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/ZCOOL_XiaoWei"
},
{
      "name": "志芒星",
      "displayName": "志芒星",
      "files": [
            "ZhiMangXing-Regular.ttf"
      ],
      "category": "chinese",
      "weights": [
            400
      ],
      "styles": [
            "normal"
      ],
      "path": "chinese/Zhi_Mang_Xing"
},
{
      "name": "HarmonyOS Sans",
      "displayName": "HarmonyOS Sans",
      "files": [
            "HarmonyOS_Sans_Thin.woff",
            "HarmonyOS_Sans_Light.woff",
            "HarmonyOS_Sans_Regular.woff",
            "HarmonyOS_Sans_Medium.woff",
            "HarmonyOS_Sans_Bold.woff",
            "HarmonyOS_Sans_Black.woff"
      ],
      "category": "english",
      "weights": [
            100,
            300,
            400,
            500,
            700,
            900
      ],
      "styles": [
            "normal"
      ],
      "path": "english/HarmonyOS_Sans"
}
    ];

    // 直接使用可用字体列表，无需再次验证
    this.fontList = fontMappings;

    console.log(`字体库初始化完成，共加载 ${this.fontList.length} 个可用字体`);
    return this.fontList;
  }

  /**
   * 检查字体可用性
   */
  async checkFontAvailability(fontMapping) {
    try {
      // 检查至少一个字体文件是否可访问
      const firstFile = fontMapping.files[0];
      const fontUrl = `${this.basePath}${fontMapping.path}/${firstFile}`;
      
      const response = await fetch(fontUrl, { method: 'HEAD' });
      return response.ok;
    } catch (error) {
      console.warn(`字体检查失败: ${fontMapping.displayName}`, error);
      return false;
    }
  }

  /**
   * 获取字体列表
   */
  getFontList() {
    return this.fontList;
  }

  /**
   * 按类别获取字体
   */
  getFontsByCategory(category) {
    return this.fontList.filter(font => font.category === category);
  }

  /**
   * 搜索字体
   */
  searchFonts(query) {
    const lowerQuery = query.toLowerCase();
    return this.fontList.filter(font => 
      font.name.toLowerCase().includes(lowerQuery) ||
      font.displayName.toLowerCase().includes(lowerQuery)
    );
  }

  /**
   * 获取字体的CSS font-family名称
   */
  getFontFamily(font) {
    return font.displayName; // 使用displayName作为font-family
  }
}

// 导出字体库管理器
if (typeof module !== 'undefined' && module.exports) {
  module.exports = FontLibraryManager;
} else {
  window.FontLibraryManager = FontLibraryManager;
}