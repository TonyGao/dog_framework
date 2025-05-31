#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
字体文件整理脚本
用于整理 /public/fonts/google_fonts 目录下的字体文件
将字体按每种字体一个目录存放，生成可用和不可用字体报告
"""

import os
import shutil
import json
import zipfile
from pathlib import Path
from collections import defaultdict

class FontOrganizer:
    def __init__(self, base_path):
        self.base_path = Path(base_path)
        self.google_fonts_path = self.base_path / 'public' / 'fonts' / 'google_fonts'
        self.organized_path = self.base_path / 'public' / 'fonts' / 'organized_fonts'
        self.backup_path = self.base_path / 'public' / 'fonts' / 'backup_original'
        
        # 字体文件扩展名
        self.font_extensions = {'.ttf', '.otf', '.woff', '.woff2'}
        
        # 字体映射和分类
        self.font_mappings = {
            # 中文字体
            'chinese': {
                'Noto_Sans_SC': {
                    'display_name': '思源黑体 简体',
                    'files': ['NotoSansSC-VariableFont_wght.ttf'],
                    'weights': [100, 200, 300, 400, 500, 600, 700, 800, 900]
                },
                'Noto_Sans_HK': {
                    'display_name': '思源黑体 繁体',
                    'files': ['NotoSansHK-VariableFont_wght.ttf'],
                    'weights': [100, 200, 300, 400, 500, 600, 700, 800, 900]
                },
                'Source_Han_Sans_SC': {
                    'display_name': '思源黑体 SC',
                    'files': ['SourceHanSansSC-VF.ttf'],
                    'weights': [100, 200, 300, 400, 500, 600, 700, 800, 900]
                },
                'Source_Han_Serif_SC': {
                    'display_name': '思源宋体 SC',
                    'files': ['SourceHanSerifSC-VF.ttf'],
                    'weights': [100, 200, 300, 400, 500, 600, 700, 800, 900]
                },
                'HarmonyOS_Sans_SC': {
                    'display_name': '鸿蒙黑体',
                    'files': [
                        'HarmonyOS_Sans_SC_Thin.ttf',
                        'HarmonyOS_Sans_SC_Light.ttf',
                        'HarmonyOS_Sans_SC_Regular.ttf',
                        'HarmonyOS_Sans_SC_Medium.ttf',
                        'HarmonyOS_Sans_SC_Bold.ttf',
                        'HarmonyOS_Sans_SC_Black.ttf'
                    ],
                    'weights': [100, 300, 400, 500, 700, 900]
                },
                'AlibabaPuHuiTi': {
                    'display_name': '阿里巴巴普惠体',
                    'files': [],  # 将从目录中扫描
                    'weights': [100, 200, 300, 400, 500, 600, 700, 800, 900]
                },
                'Liu_Jian_Mao_Cao': {
                    'display_name': '刘建毛草',
                    'files': ['LiuJianMaoCao-Regular.ttf'],
                    'weights': [400]
                },
                'Long_Cang': {
                    'display_name': '龙藏体',
                    'files': ['LongCang-Regular.ttf'],
                    'weights': [400]
                },
                'Ma_Shan_Zheng': {
                    'display_name': '马善政楷书',
                    'files': ['MaShanZheng-Regular.ttf'],
                    'weights': [400]
                },
                'ZCOOL_KuaiLe': {
                    'display_name': '站酷快乐体',
                    'files': ['ZCOOLKuaiLe-Regular.ttf'],
                    'weights': [400]
                },
                'ZCOOL_QingKe_HuangYou': {
                    'display_name': '站酷庆科黄油体',
                    'files': ['ZCOOLQingKeHuangYou-Regular.ttf'],
                    'weights': [400]
                },
                'ZCOOL_XiaoWei': {
                    'display_name': '站酷小薇',
                    'files': ['ZCOOLXiaoWei-Regular.ttf'],
                    'weights': [400]
                },
                'Zhi_Mang_Xing': {
                    'display_name': '志芒星',
                    'files': ['ZhiMangXing-Regular.ttf'],
                    'weights': [400]
                }
            },
            # 英文字体
            'english': {
                'Noto_Sans_Mono': {
                    'display_name': 'Noto Sans Mono',
                    'files': ['NotoSansMono-VariableFont_wdth_wght.ttf'],
                    'weights': [100, 200, 300, 400, 500, 600, 700, 800, 900]
                },
                'HarmonyOS_Sans': {
                    'display_name': 'HarmonyOS Sans',
                    'files': [
                        'HarmonyOS_Sans_Thin.woff',
                        'HarmonyOS_Sans_Light.woff',
                        'HarmonyOS_Sans_Regular.woff',
                        'HarmonyOS_Sans_Medium.woff',
                        'HarmonyOS_Sans_Bold.woff',
                        'HarmonyOS_Sans_Black.woff'
                    ],
                    'weights': [100, 300, 400, 500, 700, 900]
                }
            }
        }
        
        self.available_fonts = []
        self.unavailable_fonts = []
        self.organized_fonts = {}
    
    def create_directories(self):
        """创建必要的目录"""
        print("创建目录结构...")
        self.organized_path.mkdir(parents=True, exist_ok=True)
        self.backup_path.mkdir(parents=True, exist_ok=True)
        
        # 为每个字体类别创建目录
        for category in self.font_mappings:
            category_path = self.organized_path / category
            category_path.mkdir(exist_ok=True)
    
    def backup_original_files(self):
        """备份原始文件"""
        print("备份原始字体文件...")
        if self.google_fonts_path.exists():
            backup_target = self.backup_path / 'google_fonts_original'
            if not backup_target.exists():
                shutil.copytree(self.google_fonts_path, backup_target)
                print(f"原始文件已备份到: {backup_target}")
            else:
                print("备份已存在，跳过备份步骤")
    
    def extract_zip_files(self):
        """解压所有zip文件"""
        print("解压zip文件...")
        zip_files = list(self.google_fonts_path.glob('*.zip'))
        
        for zip_file in zip_files:
            try:
                extract_path = self.google_fonts_path / zip_file.stem
                if not extract_path.exists():
                    with zipfile.ZipFile(zip_file, 'r') as zip_ref:
                        zip_ref.extractall(extract_path)
                    print(f"已解压: {zip_file.name}")
                else:
                    print(f"目录已存在，跳过: {zip_file.name}")
            except Exception as e:
                print(f"解压失败 {zip_file.name}: {e}")
    
    def scan_font_files(self):
        """扫描所有字体文件"""
        print("扫描字体文件...")
        found_files = defaultdict(list)
        
        # 递归扫描所有字体文件
        for file_path in self.google_fonts_path.rglob('*'):
            if file_path.is_file() and file_path.suffix.lower() in self.font_extensions:
                # 排除备份目录和临时文件
                if '__MACOSX' not in str(file_path) and not file_path.name.startswith('.'):
                    relative_path = file_path.relative_to(self.google_fonts_path)
                    found_files[file_path.name].append({
                        'path': file_path,
                        'relative_path': str(relative_path),
                        'size': file_path.stat().st_size
                    })
        
        return found_files
    
    def organize_fonts_by_family(self, found_files):
        """按字体家族组织字体文件"""
        print("按字体家族组织文件...")
        
        for category, fonts in self.font_mappings.items():
            category_path = self.organized_path / category
            
            for font_family, font_info in fonts.items():
                font_family_path = category_path / font_family
                font_family_path.mkdir(exist_ok=True)
                
                family_files = []
                missing_files = []
                
                # 检查预定义的文件
                for expected_file in font_info['files']:
                    file_found = False
                    for file_name, file_list in found_files.items():
                        if expected_file in file_name or file_name in expected_file:
                            # 找到匹配的文件，复制到目标目录
                            for file_info in file_list:
                                target_file = font_family_path / file_name
                                if not target_file.exists():
                                    shutil.copy2(file_info['path'], target_file)
                                    print(f"复制: {file_info['relative_path']} -> {font_family}/{file_name}")
                                
                                family_files.append({
                                    'name': file_name,
                                    'path': str(target_file.relative_to(self.organized_path)),
                                    'size': file_info['size']
                                })
                                file_found = True
                                break
                    
                    if not file_found:
                        missing_files.append(expected_file)
                
                # 对于阿里巴巴普惠体等需要特殊处理的字体
                if font_family == 'AlibabaPuHuiTi':
                    alibaba_files = []
                    for file_name, file_list in found_files.items():
                        if 'AlibabaPuHuiTi' in file_name or 'Alibaba' in file_name:
                            for file_info in file_list:
                                target_file = font_family_path / file_name
                                if not target_file.exists():
                                    shutil.copy2(file_info['path'], target_file)
                                    print(f"复制: {file_info['relative_path']} -> {font_family}/{file_name}")
                                
                                alibaba_files.append({
                                    'name': file_name,
                                    'path': str(target_file.relative_to(self.organized_path)),
                                    'size': file_info['size']
                                })
                    family_files.extend(alibaba_files)
                
                # 记录字体家族信息
                self.organized_fonts[f"{category}/{font_family}"] = {
                    'display_name': font_info['display_name'],
                    'category': category,
                    'files': family_files,
                    'missing_files': missing_files,
                    'weights': font_info['weights'],
                    'available': len(family_files) > 0
                }
                
                if len(family_files) > 0:
                    self.available_fonts.append(f"{category}/{font_family}")
                else:
                    self.unavailable_fonts.append(f"{category}/{font_family}")
    
    def generate_reports(self):
        """生成字体报告"""
        print("生成字体报告...")
        
        # 生成详细报告
        report = {
            'summary': {
                'total_fonts': len(self.organized_fonts),
                'available_fonts': len(self.available_fonts),
                'unavailable_fonts': len(self.unavailable_fonts),
                'organization_date': str(Path().cwd())
            },
            'available_fonts': [],
            'unavailable_fonts': [],
            'detailed_info': self.organized_fonts
        }
        
        # 可用字体列表
        for font_key in self.available_fonts:
            font_info = self.organized_fonts[font_key]
            report['available_fonts'].append({
                'key': font_key,
                'display_name': font_info['display_name'],
                'category': font_info['category'],
                'file_count': len(font_info['files']),
                'weights': font_info['weights']
            })
        
        # 不可用字体列表
        for font_key in self.unavailable_fonts:
            font_info = self.organized_fonts[font_key]
            report['unavailable_fonts'].append({
                'key': font_key,
                'display_name': font_info['display_name'],
                'category': font_info['category'],
                'missing_files': font_info['missing_files']
            })
        
        # 保存报告
        report_file = self.base_path / 'public' / 'fonts' / 'font_organization_report.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump(report, f, ensure_ascii=False, indent=2)
        
        print(f"详细报告已保存到: {report_file}")
        
        # 生成简要报告
        summary_file = self.base_path / 'public' / 'fonts' / 'font_summary.txt'
        with open(summary_file, 'w', encoding='utf-8') as f:
            f.write("字体整理报告\n")
            f.write("=" * 50 + "\n\n")
            f.write(f"总字体数量: {len(self.organized_fonts)}\n")
            f.write(f"可用字体: {len(self.available_fonts)}\n")
            f.write(f"不可用字体: {len(self.unavailable_fonts)}\n\n")
            
            f.write("可用字体列表:\n")
            f.write("-" * 30 + "\n")
            for font_info in report['available_fonts']:
                f.write(f"- {font_info['display_name']} ({font_info['key']})\n")
                f.write(f"  文件数量: {font_info['file_count']}\n")
                f.write(f"  支持字重: {font_info['weights']}\n\n")
            
            f.write("不可用字体列表:\n")
            f.write("-" * 30 + "\n")
            for font_info in report['unavailable_fonts']:
                f.write(f"- {font_info['display_name']} ({font_info['key']})\n")
                f.write(f"  缺失文件: {font_info['missing_files']}\n\n")
        
        print(f"简要报告已保存到: {summary_file}")
        
        return report
    
    def generate_updated_css(self, report):
        """生成更新的CSS文件"""
        print("生成更新的CSS文件...")
        
        css_content = ["/* 更新的字体CSS文件 */", "/* 基于整理后的字体目录结构生成 */", ""]
        
        # 按类别生成CSS
        for category in ['chinese', 'english']:
            css_content.append(f"/* {category.upper()} FONTS */")
            
            for font_key in self.available_fonts:
                if font_key.startswith(category):
                    font_info = self.organized_fonts[font_key]
                    font_family = font_key.split('/')[-1]
                    
                    # 为每个字重生成@font-face规则
                    for weight in font_info['weights']:
                        # 查找对应字重的文件
                        weight_file = None
                        for file_info in font_info['files']:
                            file_name = file_info['name'].lower()
                            if (str(weight) in file_name or 
                                (weight == 100 and 'thin' in file_name) or
                                (weight == 300 and 'light' in file_name) or
                                (weight == 400 and ('regular' in file_name or 'normal' in file_name)) or
                                (weight == 500 and 'medium' in file_name) or
                                (weight == 700 and 'bold' in file_name) or
                                (weight == 900 and ('black' in file_name or 'heavy' in file_name))):
                                weight_file = file_info
                                break
                        
                        # 如果没找到特定字重文件，使用第一个文件（通常是可变字体）
                        if not weight_file and font_info['files']:
                            weight_file = font_info['files'][0]
                        
                        if weight_file:
                            file_ext = Path(weight_file['name']).suffix.lower()
                            format_map = {
                                '.ttf': 'truetype',
                                '.otf': 'opentype',
                                '.woff': 'woff',
                                '.woff2': 'woff2'
                            }
                            
                            css_content.extend([
                                "@font-face {",
                                f"  font-family: '{font_info['display_name']}';" if category == 'chinese' else f"  font-family: '{font_family.replace('_', ' ')}';" ,
                                f"  src: url('/fonts/organized_fonts/{font_key}/{weight_file['name']}') format('{format_map.get(file_ext, 'truetype')}');",
                                f"  font-weight: {weight};",
                                "  font-style: normal;",
                                "  font-display: swap;",
                                "}",
                                ""
                            ])
            
            css_content.append("")
        
        # 保存新的CSS文件
        new_css_file = self.base_path / 'public' / 'fonts' / 'fonts_organized.css'
        with open(new_css_file, 'w', encoding='utf-8') as f:
            f.write('\n'.join(css_content))
        
        print(f"新的CSS文件已保存到: {new_css_file}")
    
    def generate_updated_js(self, report):
        """生成更新的JavaScript字体库管理文件"""
        print("生成更新的JavaScript文件...")
        
        js_content = [
            "/**",
            " * 更新的字体库管理器",
            " * 基于整理后的字体目录结构生成",
            " */",
            "",
            "class FontLibraryManager {",
            "  constructor() {",
            "    this.fontList = [];",
            "    this.basePath = '/fonts/organized_fonts/';",
            "  }",
            "",
            "  /**",
            "   * 初始化字体库",
            "   */",
            "  async initFontLibrary() {",
            "    // 字体映射配置",
            "    const fontMappings = ["
        ]
        
        # 生成字体映射数组
        font_mappings = []
        for font_key in self.available_fonts:
            font_info = self.organized_fonts[font_key]
            font_family = font_key.split('/')[-1]
            
            mapping = {
                'name': font_family.replace('_', ' '),
                'displayName': font_info['display_name'],
                'files': [f['name'] for f in font_info['files']],
                'category': font_info['category'],
                'weights': font_info['weights'],
                'styles': ['normal'],
                'path': font_key
            }
            
            font_mappings.append(json.dumps(mapping, ensure_ascii=False, indent=6))
        
        js_content.append(',\n'.join(font_mappings))
        
        js_content.extend([
            "    ];",
            "",
            "    // 验证字体文件可用性",
            "    for (const fontMapping of fontMappings) {",
            "      const isAvailable = await this.checkFontAvailability(fontMapping);",
            "      if (isAvailable) {",
            "        this.fontList.push(fontMapping);",
            "      }",
            "    }",
            "",
            "    console.log(`字体库初始化完成，共加载 ${this.fontList.length} 个字体`);",
            "    return this.fontList;",
            "  }",
            "",
            "  /**",
            "   * 检查字体可用性",
            "   */",
            "  async checkFontAvailability(fontMapping) {",
            "    try {",
            "      // 检查至少一个字体文件是否可访问",
            "      const firstFile = fontMapping.files[0];",
            "      const fontUrl = `${this.basePath}${fontMapping.path}/${firstFile}`;",
            "      ",
            "      const response = await fetch(fontUrl, { method: 'HEAD' });",
            "      return response.ok;",
            "    } catch (error) {",
            "      console.warn(`字体检查失败: ${fontMapping.displayName}`, error);",
            "      return false;",
            "    }",
            "  }",
            "",
            "  /**",
            "   * 获取字体列表",
            "   */",
            "  getFontList() {",
            "    return this.fontList;",
            "  }",
            "",
            "  /**",
            "   * 按类别获取字体",
            "   */",
            "  getFontsByCategory(category) {",
            "    return this.fontList.filter(font => font.category === category);",
            "  }",
            "",
            "  /**",
            "   * 搜索字体",
            "   */",
            "  searchFonts(query) {",
            "    const lowerQuery = query.toLowerCase();",
            "    return this.fontList.filter(font => ",
            "      font.name.toLowerCase().includes(lowerQuery) ||",
            "      font.displayName.toLowerCase().includes(lowerQuery)",
            "    );",
            "  }",
            "}",
            "",
            "// 导出字体库管理器",
            "if (typeof module !== 'undefined' && module.exports) {",
            "  module.exports = FontLibraryManager;",
            "} else {",
            "  window.FontLibraryManager = FontLibraryManager;",
            "}"
        ])
        
        # 保存新的JS文件
        new_js_file = self.base_path / 'public' / 'fonts' / 'font_library_manager_organized.js'
        with open(new_js_file, 'w', encoding='utf-8') as f:
            f.write('\n'.join(js_content))
        
        print(f"新的JavaScript文件已保存到: {new_js_file}")
    
    def run(self):
        """运行整理流程"""
        print("开始字体文件整理流程...")
        print(f"工作目录: {self.base_path}")
        print(f"源目录: {self.google_fonts_path}")
        print(f"目标目录: {self.organized_path}")
        
        try:
            # 1. 创建目录结构
            self.create_directories()
            
            # 2. 备份原始文件
            self.backup_original_files()
            
            # 3. 解压zip文件
            self.extract_zip_files()
            
            # 4. 扫描字体文件
            found_files = self.scan_font_files()
            print(f"找到 {len(found_files)} 个字体文件")
            
            # 5. 按字体家族组织文件
            self.organize_fonts_by_family(found_files)
            
            # 6. 生成报告
            report = self.generate_reports()
            
            # 7. 生成更新的CSS和JS文件
            self.generate_updated_css(report)
            self.generate_updated_js(report)
            
            print("\n=" * 60)
            print("字体整理完成！")
            print(f"总字体数量: {len(self.organized_fonts)}")
            print(f"可用字体: {len(self.available_fonts)}")
            print(f"不可用字体: {len(self.unavailable_fonts)}")
            print("=" * 60)
            
            return True
            
        except Exception as e:
            print(f"整理过程中出现错误: {e}")
            import traceback
            traceback.print_exc()
            return False

if __name__ == '__main__':
    # 设置工作目录
    base_path = '/Users/tony/projects/enterprise_framework'
    
    # 创建字体整理器实例
    organizer = FontOrganizer(base_path)
    
    # 运行整理流程
    success = organizer.run()
    
    if success:
        print("\n字体整理成功完成！")
        print("请查看以下文件：")
        print("- /public/fonts/font_organization_report.json (详细报告)")
        print("- /public/fonts/font_summary.txt (简要报告)")
        print("- /public/fonts/fonts_organized.css (新的CSS文件)")
        print("- /public/fonts/font_library_manager_organized.js (新的JS文件)")
        print("- /public/fonts/organized_fonts/ (整理后的字体目录)")
    else:
        print("\n字体整理失败，请检查错误信息。")