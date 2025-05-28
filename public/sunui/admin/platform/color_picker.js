/**
 * 颜色选择器组件
 * 提供标准web颜色选择和自定义颜色功能
 */
class ColorPicker {
  /**
   * 构造函数
   * @param {Object} options - 配置选项
   * @param {string} options.container - 容器选择器或DOM元素
   * @param {Function} options.onChange - 颜色变化时的回调函数
   * @param {Function} options.onClose - 关闭选择器时的回调函数
   * @param {string} options.defaultColor - 默认颜色
   */
  constructor(options) {
    this.container = typeof options.container === 'string' 
      ? document.querySelector(options.container) 
      : options.container;
    this.onChange = options.onChange || function() {};
    this.onClose = options.onClose || function() {};
    this.defaultColor = options.defaultColor || '#000000';
    this.currentColor = this.defaultColor;
    this.isOpen = false;
    this.element = null;
    
    // 标准web颜色
    this.standardColors = [
      // 第一行：基础颜色
      '#000000', '#434343', '#666666', '#999999', '#b7b7b7', '#cccccc', '#d9d9d9', '#efefef', '#f3f3f3', '#ffffff',
      // 第二行：红色系
      '#980000', '#ff0000', '#ff9900', '#ffff00', '#00ff00', '#00ffff', '#4a86e8', '#0000ff', '#9900ff', '#ff00ff',
      // 第三行：浅色系
      '#e6b8af', '#f4cccc', '#fce5cd', '#fff2cc', '#d9ead3', '#d0e0e3', '#c9daf8', '#cfe2f3', '#d9d2e9', '#ead1dc',
      // 第四行：中间色系
      '#dd7e6b', '#ea9999', '#f9cb9c', '#ffe599', '#b6d7a8', '#a2c4c9', '#a4c2f4', '#9fc5e8', '#b4a7d6', '#d5a6bd',
      // 第五行：深色系
      '#cc4125', '#e06666', '#f6b26b', '#ffd966', '#93c47d', '#76a5af', '#6d9eeb', '#6fa8dc', '#8e7cc3', '#c27ba0',
      // 第六行：更深色系
      '#a61c00', '#cc0000', '#e69138', '#f1c232', '#6aa84f', '#45818e', '#3c78d8', '#3d85c6', '#674ea7', '#a64d79',
      // 第七行：最深色系
      '#85200c', '#990000', '#b45f06', '#bf9000', '#38761d', '#134f5c', '#1155cc', '#0b5394', '#351c75', '#741b47'
    ];
    
    this.init();
  }
  
  /**
   * 初始化组件
   */
  init() {
    // 创建DOM元素
    this.createElement();
    
    // 绑定事件处理函数到this
    this.handleDocumentClick = this.handleDocumentClick.bind(this);
    this.handleColorSelect = this.handleColorSelect.bind(this);
    this.handleCustomColorChange = this.handleCustomColorChange.bind(this);
    this.handleCustomColorSubmit = this.handleCustomColorSubmit.bind(this);
    
    // 添加事件监听
    document.addEventListener('click', this.handleDocumentClick);
  }
  
  /**
   * 创建颜色选择器DOM元素
   */
  createElement() {
    // 创建主容器
    this.element = document.createElement('div');
    this.element.className = 'color-picker';
    this.element.style.display = 'none';
    
    // 创建标题
    const title = document.createElement('div');
    title.className = 'color-picker-title';
    title.textContent = '选择颜色';
    this.element.appendChild(title);
    
    // 创建标准颜色区域
    const standardColorsContainer = document.createElement('div');
    standardColorsContainer.className = 'standard-colors';
    
    // 添加标准颜色选项
    this.standardColors.forEach(color => {
      const colorOption = document.createElement('div');
      colorOption.className = 'color-option';
      colorOption.style.backgroundColor = color;
      colorOption.setAttribute('data-color', color);
      colorOption.addEventListener('click', () => this.handleColorSelect(color));
      standardColorsContainer.appendChild(colorOption);
    });
    
    this.element.appendChild(standardColorsContainer);
    
    // 创建自定义颜色区域
    const customColorContainer = document.createElement('div');
    customColorContainer.className = 'custom-color';
    
    // 当前选中的颜色预览
    const colorPreview = document.createElement('div');
    colorPreview.className = 'color-preview';
    colorPreview.style.backgroundColor = this.currentColor;
    this.colorPreview = colorPreview;
    customColorContainer.appendChild(colorPreview);
    
    // 自定义颜色输入
    const customColorInput = document.createElement('div');
    customColorInput.className = 'custom-color-input';
    
    // 颜色选择器
    const colorInput = document.createElement('input');
    colorInput.type = 'color';
    colorInput.value = this.currentColor;
    colorInput.addEventListener('input', (e) => this.handleCustomColorChange(e));
    colorInput.addEventListener('change', (e) => this.handleCustomColorSubmit(e));
    this.colorInput = colorInput;
    
    // 颜色代码输入框
    const hexInput = document.createElement('input');
    hexInput.type = 'text';
    hexInput.className = 'hex-input';
    hexInput.value = this.currentColor;
    hexInput.placeholder = '#000000';
    hexInput.addEventListener('input', e => {
      const value = e.target.value;
      if (/^#[0-9A-F]{6}$/i.test(value)) {
        this.handleCustomColorChange({ target: { value } });
      }
    });
    hexInput.addEventListener('blur', () => {
      if (!/^#[0-9A-F]{6}$/i.test(hexInput.value)) {
        hexInput.value = this.currentColor;
      }
    });
    this.hexInput = hexInput;
    
    customColorInput.appendChild(colorInput);
    customColorInput.appendChild(hexInput);
    customColorContainer.appendChild(customColorInput);
    
    // 按钮区域
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'button-container';
    
    // 确定按钮
    const applyButton = document.createElement('button');
    applyButton.className = 'apply-button';
    applyButton.textContent = '确定';
    applyButton.addEventListener('click', () => {
      this.onChange(this.currentColor);
      this.close();
    });
    
    // 取消按钮
    const cancelButton = document.createElement('button');
    cancelButton.className = 'cancel-button';
    cancelButton.textContent = '取消';
    cancelButton.addEventListener('click', () => this.close());
    
    buttonContainer.appendChild(applyButton);
    buttonContainer.appendChild(cancelButton);
    customColorContainer.appendChild(buttonContainer);
    
    this.element.appendChild(customColorContainer);
    
    // 添加到容器
    this.container.appendChild(this.element);
    
    // 添加样式
    this.addStyles();
  }
  
  /**
   * 添加组件样式
   */
  addStyles() {
    if (!document.getElementById('color-picker-styles')) {
      const style = document.createElement('style');
      style.id = 'color-picker-styles';
      style.textContent = `
        .color-picker {
          position: absolute;
          width: 300px;
          background-color: #ffffff;
          border-radius: 8px;
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
          padding: 15px;
          z-index: 1000;
          font-family: Arial, sans-serif;
        }
        
        .color-picker .color-picker-title {
          font-size: 16px;
          font-weight: bold;
          margin-bottom: 15px;
          color: #333;
          text-align: center;
        }
        
        .color-picker .standard-colors {
          display: grid;
          grid-template-columns: repeat(10, 1fr);
          gap: 5px;
          margin-bottom: 15px;
        }
        
        .color-picker .color-option {
          width: 20px;
          height: 20px;
          border-radius: 3px;
          cursor: pointer;
          transition: transform 0.1s;
          border: 1px solid #ddd;
        }
        
        .color-picker .color-option:hover {
          transform: scale(1.1);
          box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }
        
        .color-picker .custom-color {
          padding: 10px 0;
          border-top: 1px solid #eee;
        }
        
        .color-picker .color-preview {
          width: 30px;
          height: 30px;
          border-radius: 4px;
          margin-right: 10px;
          border: 1px solid #ddd;
          display: inline-block;
          vertical-align: middle;
        }
        
        .color-picker .custom-color-input {
          display: inline-block;
          vertical-align: middle;
          width: calc(100% - 50px);
        }
        
        .color-picker input[type="color"] {
          width: 40px;
          height: 30px;
          border: none;
          padding: 0;
          background: none;
          cursor: pointer;
          vertical-align: middle;
        }
        
        .color-picker .hex-input {
          width: calc(100% - 50px);
          height: 30px;
          border: 1px solid #ddd;
          border-radius: 4px;
          padding: 0 8px;
          margin-left: 5px;
          vertical-align: middle;
          font-size: 14px;
        }
        
        .color-picker .button-container {
          display: flex;
          justify-content: flex-end;
          margin-top: 15px;
        }
        
        .color-picker button {
          padding: 6px 12px;
          border-radius: 4px;
          border: none;
          cursor: pointer;
          font-size: 14px;
          margin-left: 10px;
        }
        
        .color-picker .apply-button {
          background-color: #4a86e8;
          color: white;
        }
        
        .color-picker .apply-button:hover {
          background-color: #3a76d8;
        }
        
        .color-picker .cancel-button {
          background-color: #f1f1f1;
          color: #333;
        }
        
        .color-picker .cancel-button:hover {
          background-color: #e1e1e1;
        }
      `;
      document.head.appendChild(style);
    }
  }
  
  /**
   * 处理标准颜色选择
   * @param {string} color - 选中的颜色
   */
  handleColorSelect(color) {
    this.currentColor = color;
    this.colorPreview.style.backgroundColor = color;
    this.colorInput.value = color;
    this.hexInput.value = color;
  }
  
  /**
   * 处理自定义颜色变化
   * @param {Event} e - 输入事件
   */
  handleCustomColorChange(e) {
    const color = e.target.value;
    this.currentColor = color;
    
    // 添加检查确保 colorPreview 存在
    if (this.colorPreview) {
      this.colorPreview.style.backgroundColor = color;
    }
    
    // 添加检查确保 hexInput 存在
    if (this.hexInput) {
      this.hexInput.value = color;
    }
    
    // 添加检查确保 colorInput 存在
    if (this.colorInput) {
      this.colorInput.value = color;
    }
  }
  
  /**
   * 处理自定义颜色提交
   */
  handleCustomColorSubmit(e) {
    const color = e.target.value;
    if (/^#[0-9A-F]{6}$/i.test(color)) {
      this.handleColorSelect(color);
    }
  }
  
  /**
   * 处理文档点击事件，用于关闭选择器
   * @param {Event} e - 点击事件
   */
  handleDocumentClick(e) {
    if (this.isOpen && !this.element.contains(e.target) && 
        (this.triggerElement && !this.triggerElement.contains(e.target))) {
      this.close();
    }
  }
  
  /**
   * 打开颜色选择器
   * @param {HTMLElement} triggerElement - 触发打开的元素
   */
  open(triggerElement) {
    this.triggerElement = triggerElement;
    this.isOpen = true;
    this.element.style.display = 'block';
    
    // 定位选择器
    const rect = triggerElement.getBoundingClientRect();
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const scrollLeft = window.scrollX || document.documentElement.scrollLeft;
    
    // 计算位置，确保选择器在视口内
    const top = rect.bottom + scrollTop;
    const left = rect.left + scrollLeft;
    
    this.element.style.top = `${top}px`;
    this.element.style.left = `${left}px`;
    
    // 检查是否超出右边界
    const rightEdge = left + this.element.offsetWidth;
    const windowWidth = window.innerWidth + scrollLeft;
    if (rightEdge > windowWidth) {
      this.element.style.left = `${windowWidth - this.element.offsetWidth - 10}px`;
    }
    
    // 检查是否超出下边界
    const bottomEdge = top + this.element.offsetHeight;
    const windowHeight = window.innerHeight + scrollTop;
    if (bottomEdge > windowHeight) {
      this.element.style.top = `${rect.top + scrollTop - this.element.offsetHeight}px`;
    }
  }
  
  /**
   * 关闭颜色选择器
   */
  close() {
    this.isOpen = false;
    this.element.style.display = 'none';
    this.onClose();
  }
  
  /**
   * 设置当前颜色
   * @param {string} color - 颜色值
   */
  setColor(color) {
    this.currentColor = color;
    this.colorPreview.style.backgroundColor = color;
    this.colorInput.value = color;
    this.hexInput.value = color;
  }
  
  /**
   * 获取当前颜色
   * @returns {string} 当前颜色值
   */
  getColor() {
    return this.currentColor;
  }
  
  /**
   * 销毁组件
   */
  destroy() {
    document.removeEventListener('click', this.handleDocumentClick);
    if (this.element && this.element.parentNode) {
      this.element.parentNode.removeChild(this.element);
    }
  }
}

// 导出组件
window.ColorPicker = ColorPicker;