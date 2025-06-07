/**
 * 字体选择器弹窗管理器
 */
class FontSelectorModal {
    constructor() {
        this.modal = null;
        this.selectedFont = null;
        this.selectedWeight = 400;
        this.onFontSelect = null;
        this.fonts = {
            chinese: [
                { name: '阿里妈妈东方大楷', family: '阿里妈妈东方大楷', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: '阿里妈妈刀隶体', family: '阿里妈妈刀隶体', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: '阿里妈妈方圆体', family: '阿里妈妈方圆体', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: '阿里妈妈数黑体', family: '阿里妈妈数黑体', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: '钉钉进步体', family: '钉钉进步体', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: '阿里巴巴普惠体', family: '阿里巴巴普惠体', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: '思源黑体简体中文', family: '思源黑体简体中文', weights: [100, 300, 400, 500, 700, 900] },
                { name: '思源黑体香港', family: '思源黑体简体中文', weights: [100, 300, 400, 500, 700, 900] },
                { name: '思源宋体简体中文', family: '思源宋体简体中文', weights: [100, 300, 400, 500, 700, 900] },
                { name: '思源宋体香港', family: '思源宋体香港', weights: [100, 300, 400, 500, 700, 900] },
                { name: '钟齐流江毛草', family: '钟齐流江毛草', weights: [100, 300, 400, 500, 700, 900] },
                { name: '龙藏体', family: '龙藏体', weights: [100, 300, 400, 500, 700, 900] },
                { name: '钟齐马善政毛笔楷书', family: '钟齐马善政毛笔楷书', weights: [100, 300, 400, 500, 700, 900] },
                { name: '站酷快乐体', family: '站酷快乐体', weights: [100, 300, 400, 500, 700, 900] },
                { name: '站酷庆科黄油体', family: '站酷庆科黄油体', weights: [100, 300, 400, 500, 700, 900] },
                { name: '站酷小薇', family: '站酷小薇', weights: [100, 300, 400, 500, 700, 900] },
                { name: '志莽行书', family: '志莽行书', weights: [100, 300, 400, 500, 700, 900] },
                { name: 'Noto宋体简体中文', family: 'Noto宋体简体中文', weights: [100, 300, 400, 500, 700, 900] },
                // Windows/macOS 中文字体
                { name: '微软雅黑', family: 'Microsoft YaHei', weights: [300, 400, 700] },
                { name: '宋体', family: 'SimSun', weights: [400] },
                { name: '黑体', family: 'SimHei', weights: [400] },
                { name: '楷体', family: 'KaiTi', weights: [400] },
                { name: '仿宋', family: 'FangSong', weights: [400] },
                { name: '苹方', family: 'PingFang SC', weights: [100, 200, 300, 400, 500, 600, 700] },
                { name: '华文黑体', family: 'STHeiti', weights: [400] },
                { name: '华文楷体', family: 'STKaiti', weights: [400] },
                { name: '华文宋体', family: 'STSong', weights: [400] },
                { name: '华文仿宋', family: 'STFangsong', weights: [400] },
            ],
            english: [
                { name: 'AlimamaAgile', family: 'AlimamaAgile', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: 'Noto Sans Mono', family: 'Noto Sans Mono', weights: [100, 300, 400, 500, 700, 900] },
                { name: 'Arial', family: 'Arial', weights: [400, 700] },
                { name: 'Helvetica', family: 'Helvetica', weights: [400, 700] },
                { name: 'Times New Roman', family: 'Times New Roman', weights: [400, 700] },
                { name: 'Georgia', family: 'Georgia', weights: [400, 700] },
                { name: 'Verdana', family: 'Verdana', weights: [400, 700] },
                { name: 'Noto Sans Symbols2', family: 'NotoSansSymbols2', weights: [100, 300, 400, 500, 700, 900] },
                // Windows 常用字体
                { name: 'Calibri', family: 'Calibri', weights: [300, 400, 700] },
                { name: 'Cambria', family: 'Cambria', weights: [400, 700] },
                { name: 'Consolas', family: 'Consolas', weights: [400] },
                { name: 'Courier New', family: 'Courier New', weights: [400, 700] },
                { name: 'Tahoma', family: 'Tahoma', weights: [400, 700] },
                { name: 'Trebuchet MS', family: 'Trebuchet MS', weights: [400, 700] },
                { name: 'Comic Sans MS', family: 'Comic Sans MS', weights: [400, 700] },
                { name: 'Impact', family: 'Impact', weights: [400] },
                { name: 'Lucida Console', family: 'Lucida Console', weights: [400] },
                { name: 'Palatino Linotype', family: 'Palatino Linotype', weights: [400, 700] },
                // macOS 常用字体
                { name: 'San Francisco', family: '-apple-system', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: 'Helvetica Neue', family: 'Helvetica Neue', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
                { name: 'Avenir', family: 'Avenir', weights: [300, 400, 500, 600, 700, 800, 900] },
                { name: 'Menlo', family: 'Menlo', weights: [400, 700] },
                { name: 'Monaco', family: 'Monaco', weights: [400] },
                { name: 'Optima', family: 'Optima', weights: [400, 700] },
                { name: 'Futura', family: 'Futura', weights: [300, 400, 500, 700] },
                { name: 'Gill Sans', family: 'Gill Sans', weights: [300, 400, 600, 700] },
                { name: 'Baskerville', family: 'Baskerville', weights: [400, 600, 700] },
                { name: 'Hoefler Text', family: 'Hoefler Text', weights: [400, 700] },
            ],
            korean: [
                { name: '思源黑体韩国', family: '思源黑体韩国', weights: [100, 300, 400, 500, 700, 900] },
                { name: '思源宋体韩国', family: '思源宋体韩国', weights: [100, 300, 400, 500, 700, 900] },
                { name: 'Noto Sans KR', family: 'Noto Sans KR', weights: [100, 300, 400, 500, 700, 900] },
                { name: 'Malgun Gothic', family: 'Malgun Gothic', weights: [400, 700] },
                { name: 'Dotum', family: 'Dotum', weights: [400] },
                { name: 'Gulim', family: 'Gulim', weights: [400] },
                { name: 'Batang', family: 'Batang', weights: [400] },
                { name: 'Gungsuh', family: 'Gungsuh', weights: [400] },
                { name: 'Apple SD Gothic Neo', family: 'Apple SD Gothic Neo', weights: [100, 200, 300, 400, 500, 600, 700, 800, 900] },
            ],
            japanese: [
                { name: '思源黑体日本', family: '思源黑体日本', weights: [100, 300, 400, 500, 700, 900] },
                { name: '思源宋体日本', family: '思源宋体日本', weights: [100, 300, 400, 500, 700, 900] },
                { name: 'Noto Sans JP', family: 'Noto Sans JP', weights: [100, 300, 400, 500, 700, 900] },
                { name: 'Yu Gothic', family: 'Yu Gothic', weights: [300, 400, 500, 600, 700] },
                { name: 'Meiryo', family: 'Meiryo', weights: [400, 700] },
                { name: 'MS Gothic', family: 'MS Gothic', weights: [400] },
                { name: 'MS Mincho', family: 'MS Mincho', weights: [400] },
                { name: 'Hiragino Kaku Gothic Pro', family: 'Hiragino Kaku Gothic Pro', weights: [300, 600] },
                { name: 'Hiragino Mincho Pro', family: 'Hiragino Mincho Pro', weights: [300, 600] },
                { name: 'Osaka', family: 'Osaka', weights: [400] },
            ],
            emoji: [
                { name: 'NotoEmoji', family: 'NotoEmoji', weights: [400] },
                { name: 'Apple Color Emoji', family: 'Apple Color Emoji', weights: [400] },
                { name: 'Segoe UI Emoji', family: 'Segoe UI Emoji', weights: [400] },
                { name: 'Noto Color Emoji', family: 'Noto Color Emoji', weights: [400] },
            ]
        };
        this.init();
    }

    init() {
        this.createModal();
        this.bindEvents();
    }

    createModal() {
        const modalHTML = `
            <div class="font-selector-modal" id="fontSelectorModal">
                <div class="font-category-tabs">
                    <div class="font-tab active" data-category="chinese">
                        <i class="fa-solid fa-font"></i>
                        <span>中文</span>
                    </div>
                    <div class="font-tab" data-category="english">
                        <i class="fa-solid fa-font"></i>
                        <span>英文</span>
                    </div>
                    <div class="font-tab" data-category="korean">
                        <i class="fa-solid fa-font"></i>
                        <span>韩文</span>
                    </div>
                    <div class="font-tab" data-category="japanese">
                        <i class="fa-solid fa-font"></i>
                        <span>日文</span>
                    </div>
                    <div class="font-tab" data-category="emoji">
                        <i class="fa-solid fa-smile"></i>
                        <span>表情</span>
                    </div>
                </div>
                <div class="font-selector-content">
                    <div class="font-selector-header">
                        <h3 class="font-selector-title">选择字体</h3>
                        <button class="font-selector-close" id="fontSelectorClose">&times;</button>
                    </div>
                    <div class="font-selector-body">
                        <div class="font-search-container">
                            <input type="text" class="font-search-input" placeholder="搜索字体..." id="fontSearchInput">
                        </div>
                        <div class="font-selector-main">
                            <div class="font-content-area">
                                <div class="font-category active" id="chineseCategory" data-category="chinese">
                                    <div class="font-grid" id="chineseFontGrid"></div>
                                </div>
                                <div class="font-category" id="englishCategory" data-category="english">
                                    <div class="font-grid" id="englishFontGrid"></div>
                                </div>
                                <div class="font-category" id="koreanCategory" data-category="korean">
                                    <div class="font-grid" id="koreanFontGrid"></div>
                                </div>
                                <div class="font-category" id="japaneseCategory" data-category="japanese">
                                    <div class="font-grid" id="japaneseFontGrid"></div>
                                </div>
                                <div class="font-category" id="emojiCategory" data-category="emoji">
                                    <div class="font-grid" id="emojiFontGrid"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="font-selector-footer">
                        <button class="btn secondary" id="fontSelectorCancel"><i class="fa-solid fa-times"></i> 取消</button>
                        <button class="btn primary" id="fontSelectorConfirm" disabled><i class="fa-solid fa-check"></i> 确定</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('fontSelectorModal');
        this.renderFonts();
        this.bindTabEvents();
        this.bindAccordionEvents();
    }

    renderFonts() {
        this.renderFontCategory('chinese', 'chineseFontGrid');
        this.renderFontCategory('english', 'englishFontGrid');
        this.renderFontCategory('korean', 'koreanFontGrid');
        this.renderFontCategory('japanese', 'japaneseFontGrid');
        this.renderFontCategory('emoji', 'emojiFontGrid');
    }

    renderFontCategory(category, containerId) {
        const container = document.getElementById(containerId);
        const fonts = this.fonts[category];
        
        container.innerHTML = fonts.map(font => {
            const weightDemos = font.weights.map(weight => {
                const weightName = this.getWeightName(weight);
                return `<span class="font-weight-demo" data-weight="${weight}" style="font-family: '${font.family}'; font-weight: ${weight}; cursor: pointer;">${weightName}</span>`;
            }).join('');
            
            let chineseText, englishText;
            if (category === 'chinese') {
                chineseText = '中文字体预览效果';
                englishText = 'English Font Preview';
            } else if (category === 'korean') {
                chineseText = '한국어 글꼴 미리보기';
                englishText = 'Korean Font Preview';
            } else if (category === 'japanese') {
                chineseText = '日本語フォントプレビュー';
                englishText = 'Japanese Font Preview';
            } else if (category === 'emoji') {
                chineseText = '😀😃😄😁😆😅😂🤣';
                englishText = '🎉🎊🎈🎁🎀🎂🎄🎃';
            } else {
                chineseText = 'Chinese Text';
                englishText = 'English Font Preview';
            }
            
            // 确定字体类型标签
            let fontTypeTag = '';
            const fontName = font.name.toLowerCase();
            const fontFamily = font.family.toLowerCase();
            
            if (this.isWebFont(fontName, fontFamily)) {
                fontTypeTag = '<span class="font-type-tag web-font">Web Font</span>';
            } else if (this.isWindowsFont(fontName, fontFamily)) {
                fontTypeTag = '<span class="font-type-tag windows-font">Windows</span>';
            } else if (this.isMacOSFont(fontName, fontFamily)) {
                fontTypeTag = '<span class="font-type-tag macos-font">macOS</span>';
            }
            
            return `
                <div class="font-item" data-font-family="${font.family}" data-font-name="${font.name}">
                    <div class="font-name">
                        ${font.name}
                        ${fontTypeTag}
                    </div>
                    <div class="font-preview">
                        <div class="font-preview-text font-preview-chinese" style="font-family: '${font.family}'; font-size: xx-large;">${chineseText}</div>
                        <div class="font-preview-text font-preview-english" style="font-family: '${font.family}'; font-size: x-large;">${englishText}</div>
                    </div>
                    <div class="font-weight-demos">${weightDemos}</div>
                </div>
            `;
        }).join('');
    }

    getWeightName(weight) {
        const weightNames = {
            100: '极细',
            200: '特细',
            300: '细体',
            400: '正常',
            500: '中等',
            600: '半粗',
            700: '粗体',
            800: '特粗',
            900: '极粗'
        };
        return weightNames[weight] || weight.toString();
    }

    isWebFont(fontName, fontFamily) {
        const webFonts = [
            '阿里妈妈', '钉钉', '阿里巴巴', '思源', '钟齐', '龙藏', '站酷', '志莽', 'noto',
            'alimamaagile', 'noto sans mono', 'noto sans symbols2', 'noto sans kr', 'noto sans jp', 
            'noto color emoji', 'twemoji', 'emojione', 'symbola'
        ];
        return webFonts.some(webFont => fontName.includes(webFont) || fontFamily.includes(webFont));
    }

    isWindowsFont(fontName, fontFamily) {
        const windowsFonts = [
            'microsoft yahei', '微软雅黑', 'simsun', '宋体', 'simhei', '黑体', 'kaiti', '楷体', 
            'fangsong', '仿宋', 'calibri', 'cambria', 'consolas', 'courier new', 'tahoma', 
            'trebuchet ms', 'comic sans ms', 'impact', 'lucida console', 'palatino linotype',
            'malgun gothic', 'dotum', 'gulim', 'batang', 'gungsuh', 'yu gothic', 'meiryo', 
            'ms gothic', 'ms mincho', 'segoe ui emoji'
        ];
        return windowsFonts.some(winFont => fontName.includes(winFont) || fontFamily.includes(winFont));
    }

    isMacOSFont(fontName, fontFamily) {
        const macosFonts = [
            'pingfang sc', '苹方', 'stheiti', '华文黑体', 'stkaiti', '华文楷体', 'stsong', '华文宋体', 
            'stfangsong', '华文仿宋', '-apple-system', 'san francisco', 'helvetica neue', 'avenir', 
            'menlo', 'monaco', 'optima', 'futura', 'gill sans', 'baskerville', 'hoefler text',
            'apple sd gothic neo', 'hiragino kaku gothic pro', 'hiragino mincho pro', 'osaka',
            'apple color emoji'
        ];
        return macosFonts.some(macFont => fontName.includes(macFont) || fontFamily.includes(macFont));
    }

    bindEvents() {
        // 关闭按钮
        document.getElementById('fontSelectorClose').addEventListener('click', () => {
            this.hide();
        });
        
        // 取消按钮
        document.getElementById('fontSelectorCancel').addEventListener('click', () => {
            this.hide();
        });
        
        // 确定按钮
        document.getElementById('fontSelectorConfirm').addEventListener('click', () => {
            if (this.selectedFont && this.onFontSelect) {
                this.onFontSelect(this.selectedFont);
            }
            this.hide();
        });
        
        // 点击背景关闭
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.hide();
            }
        });
        
        // 字体项点击
        this.modal.addEventListener('click', (e) => {
            const fontItem = e.target.closest('.font-item');
            const weightDemo = e.target.closest('.font-weight-demo');
            
            if (weightDemo) {
                // 点击字重选项
                this.selectWeight(weightDemo);
                e.stopPropagation();
            } else if (fontItem) {
                // 点击字体项
                this.selectFont(fontItem);
            }
        });
        
        // 搜索功能
        document.getElementById('fontSearchInput').addEventListener('input', (e) => {
            this.filterFonts(e.target.value);
        });
        
        // ESC键关闭
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('show')) {
                this.hide();
            }
        });
    }

    selectWeight(weightDemo) {
        // 移除同一字体项内其他字重的选中状态
        const fontItem = weightDemo.closest('.font-item');
        fontItem.querySelectorAll('.font-weight-demo.selected').forEach(demo => {
            demo.classList.remove('selected');
        });
        
        // 添加选中状态
        weightDemo.classList.add('selected');
        
        // 保存选中的字重
        this.selectedWeight = parseInt(weightDemo.dataset.weight);
        
        // 如果还没有选中字体，自动选中当前字体
        if (!this.selectedFont || this.selectedFont.family !== fontItem.dataset.fontFamily) {
            this.selectFont(fontItem);
        } else {
            // 如果已经选中了字体，更新字体的weight属性
            this.selectedFont.weight = this.selectedWeight;
        }
    }

    selectFont(fontItem) {
        // 移除之前的选中状态
        this.modal.querySelectorAll('.font-item.selected').forEach(item => {
            item.classList.remove('selected');
        });
        
        // 添加选中状态
        fontItem.classList.add('selected');
        
        // 保存选中的字体
        this.selectedFont = {
            family: fontItem.dataset.fontFamily,
            name: fontItem.dataset.fontName,
            weight: this.selectedWeight
        };
        
        // 启用确定按钮
        document.getElementById('fontSelectorConfirm').disabled = false;
    }

    filterFonts(searchTerm) {
        const fontItems = this.modal.querySelectorAll('.font-item');
        const term = searchTerm.toLowerCase();
        
        fontItems.forEach(item => {
            const fontName = item.dataset.fontName.toLowerCase();
            const fontFamily = item.dataset.fontFamily.toLowerCase();
            
            if (fontName.includes(term) || fontFamily.includes(term)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    show(callback, currentFont = null) {
        this.onFontSelect = callback;
        this.selectedFont = null;
        this.selectedWeight = 400;
        
        // 重置状态
        this.modal.querySelectorAll('.font-item.selected').forEach(item => {
            item.classList.remove('selected');
        });
        this.modal.querySelectorAll('.font-weight-demo.selected').forEach(demo => {
            demo.classList.remove('selected');
        });
        document.getElementById('fontSelectorConfirm').disabled = true;
        document.getElementById('fontSearchInput').value = '';
        this.filterFonts('');
        
        // 如果提供了当前字体信息，在弹窗中标记为选中
        if (currentFont && currentFont.family) {
            const fontItems = this.modal.querySelectorAll('.font-item');
            fontItems.forEach(item => {
                const itemFamily = item.dataset.fontFamily;
                if (itemFamily === currentFont.family || 
                    itemFamily.includes(currentFont.family) || 
                    currentFont.family.includes(itemFamily)) {
                    // 选中字体
                    item.classList.add('selected');
                    this.selectedFont = {
                        family: itemFamily,
                        name: item.dataset.fontName,
                        weight: currentFont.weight || 400
                    };
                    this.selectedWeight = currentFont.weight || 400;
                    
                    // 选中对应的字重
                    const weightDemo = item.querySelector(`[data-weight="${this.selectedWeight}"]`);
                    if (weightDemo) {
                        weightDemo.classList.add('selected');
                    }
                    
                    // 启用确定按钮
                    document.getElementById('fontSelectorConfirm').disabled = false;
                    
                    // 滚动到选中的字体
                    item.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
        
        this.modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    hide() {
        this.modal.classList.remove('show');
        document.body.style.overflow = '';
        this.selectedFont = null;
        this.selectedWeight = 400;
        this.onFontSelect = null;
    }

    bindTabEvents() {
        const tabs = this.modal.querySelectorAll('.font-tab');
        const categories = this.modal.querySelectorAll('.font-category');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetCategory = tab.dataset.category;
                
                // 移除所有活动状态
                tabs.forEach(t => t.classList.remove('active'));
                categories.forEach(c => c.classList.remove('active'));
                
                // 添加当前活动状态
                tab.classList.add('active');
                const targetCategoryElement = this.modal.querySelector(`#${targetCategory}Category`);
                if (targetCategoryElement) {
                    targetCategoryElement.classList.add('active');
                    // 滚动到对应分类
                    targetCategoryElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    bindAccordionEvents() {
        const categoryHeaders = this.modal.querySelectorAll('.font-category-header');
        
        categoryHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const category = header.dataset.category;
                const categoryElement = this.modal.querySelector(`#${category}Category`);
                const toggle = header.querySelector('.category-toggle');
                const fontGrid = categoryElement.querySelector('.font-grid');
                
                if (categoryElement.classList.contains('collapsed')) {
                    // 展开
                    categoryElement.classList.remove('collapsed');
                    toggle.classList.remove('fa-chevron-right');
                    toggle.classList.add('fa-chevron-down');
                    fontGrid.style.display = 'grid';
                } else {
                    // 折叠
                    categoryElement.classList.add('collapsed');
                    toggle.classList.remove('fa-chevron-down');
                    toggle.classList.add('fa-chevron-right');
                    fontGrid.style.display = 'none';
                }
            });
        });
    }
}

// 全局实例
window.fontSelectorModal = new FontSelectorModal();