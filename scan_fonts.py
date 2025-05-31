#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
简化的字体扫描脚本
快速扫描字体文件并生成报告
"""

import os
import json
from pathlib import Path

def scan_fonts():
    base_path = Path('/Users/tony/projects/enterprise_framework')
    google_fonts_path = base_path / 'public' / 'fonts' / 'google_fonts'
    
    print(f"扫描目录: {google_fonts_path}")
    
    if not google_fonts_path.exists():
        print("字体目录不存在")
        return
    
    # 字体文件扩展名
    font_extensions = {'.ttf', '.otf', '.woff', '.woff2'}
    
    found_fonts = []
    missing_fonts = []
    
    # 预期的字体文件
    expected_fonts = {
        'Noto Sans SC': ['NotoSansSC-VariableFont_wght.ttf'],
        'Source Han Sans SC': ['SourceHanSansSC-VF.ttf'],
        'HarmonyOS Sans SC': ['HarmonyOS_Sans_SC_Regular.ttf'],
        'Liu Jian Mao Cao': ['LiuJianMaoCao-Regular.ttf'],
        'Long Cang': ['LongCang-Regular.ttf'],
        'Ma Shan Zheng': ['MaShanZheng-Regular.ttf'],
        'ZCOOL KuaiLe': ['ZCOOLKuaiLe-Regular.ttf']
    }
    
    print("扫描字体文件...")
    
    # 扫描所有字体文件
    all_font_files = []
    for file_path in google_fonts_path.rglob('*'):
        if file_path.is_file() and file_path.suffix.lower() in font_extensions:
            if '__MACOSX' not in str(file_path) and not file_path.name.startswith('.'):
                relative_path = file_path.relative_to(google_fonts_path)
                all_font_files.append({
                    'name': file_path.name,
                    'path': str(relative_path),
                    'size': file_path.stat().st_size
                })
    
    print(f"找到 {len(all_font_files)} 个字体文件")
    
    # 检查预期字体
    for font_name, expected_files in expected_fonts.items():
        font_found = False
        for expected_file in expected_files:
            for font_file in all_font_files:
                if expected_file in font_file['name'] or font_file['name'] in expected_file:
                    found_fonts.append({
                        'font_name': font_name,
                        'file_name': font_file['name'],
                        'path': font_file['path'],
                        'size': font_file['size']
                    })
                    font_found = True
                    break
            if font_found:
                break
        
        if not font_found:
            missing_fonts.append({
                'font_name': font_name,
                'expected_files': expected_files
            })
    
    # 生成报告
    report = {
        'scan_date': str(Path().cwd()),
        'total_files': len(all_font_files),
        'found_fonts': len(found_fonts),
        'missing_fonts': len(missing_fonts),
        'available_fonts': found_fonts,
        'missing_fonts_list': missing_fonts,
        'all_font_files': all_font_files[:20]  # 只显示前20个文件
    }
    
    # 保存报告
    report_file = base_path / 'public' / 'fonts' / 'font_scan_report.json'
    with open(report_file, 'w', encoding='utf-8') as f:
        json.dump(report, f, ensure_ascii=False, indent=2)
    
    print(f"\n扫描完成!")
    print(f"总文件数: {len(all_font_files)}")
    print(f"可用字体: {len(found_fonts)}")
    print(f"缺失字体: {len(missing_fonts)}")
    print(f"报告已保存到: {report_file}")
    
    # 打印简要信息
    print("\n可用字体:")
    for font in found_fonts:
        print(f"  - {font['font_name']}: {font['file_name']}")
    
    print("\n缺失字体:")
    for font in missing_fonts:
        print(f"  - {font['font_name']}: {font['expected_files']}")

if __name__ == '__main__':
    scan_fonts()