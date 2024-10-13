class Str {
  // 在特定数组中进行模糊查询
  // [{lowerText: 'beijing', orginText: 'Beijing', id: '909575156'}]
  static searchArr (arr, keywords) {
    let keywordsArr;
    if (keywords.indexOf(' ') >= 0) {
      keywordsArr = _.uniqWith(_.split(keywords, ' ').filter(n => n));
    }
    let result = [];
    for (let i = 0; i < arr.length; i++) {
      if (keywords.indexOf(' ') >= 0) {
        $.each(keywordsArr, function (idx, ele) {
          if (arr[i]['lowerText'].indexOf(ele) >= 0) {
            result.push(arr[i]);
          }
        })
      } else {
        if (arr[i]['lowerText'].indexOf(keywords) >= 0) {
          result.push(arr[i]);
        }
      }
    }
    result = _.uniqWith(result);
    return result;
  }

  /**
   * 将 "ce~shi~hao" 转化为 "ceShiHao"
   * @param {*} str 
   * @returns 
   */
  static toCamelCase (str) {
    return str.replace(/(~\w)/g, function (match) {
      return match.charAt(1).toUpperCase();
    });
  }

  /**
   * 将首字母进行小写化
   * @param {*} str 
   * @returns 
   */
  static firstLetterToLowerCase (str) {
    return str.charAt(0).toLowerCase() + str.slice(1);
  }

  // 将序列化的表单数据转换为JSON对象
  static serializeToJson (serializedForm) {
    const formDataArray = serializedForm.split('&');
    const formDataJson = {};
    formDataArray.forEach(item => {
      const pair = item.split('=');
      formDataJson[pair[0]] = decodeURIComponent(pair[1] || '');
    });
    return formDataJson;
  }

  // 用于生成指定长度的随机字符串的函数
  static generateRandomString (length) {
    const chars = '0123456789abcdef';
    let result = '';
    for (let i = 0; i < length; i++) {
      result += chars[Math.floor(Math.random() * chars.length)];
    }
    return result;
  }

  static formatToPascalStyle(str) {
    return _.chain(str)
      .trim() // 去除首尾空格
      .replace(/\s+/g, '_') // 将空格替换为下划线
      .replace(/[-]+/g, '_') // 将短横线替换为下划线
      .snakeCase() // 将字符串转换为 snake_case 格式
      .split('_') // 按下划线分割
      .map(_.capitalize) // 将每个单词的首字母大写
      .join('') // 连接成字符串
      .value(); // 获取最终结果
  }

  /**
   * 将驼峰命名的实体类名称转换为数据库表名格式。
   * @param {string} className - 实体类名称，如 "HelloWorld"。
   * @returns {string} - 转换后的数据库表名，如 "hello_world"。
   */
  static tableize(className) {
    // 匹配驼峰命名中的大写字母，并在前面插入下划线，再将整个字符串转换为小写
    return className
      .replace(/([a-z])([A-Z])/g, '$1_$2')  // 在小写字母和大写字母之间插入下划线
      .replace(/([A-Z])([A-Z][a-z])/g, '$1_$2')  // 处理连续大写字母的分隔，如 "FOOBar" -> "foo_bar"
      .toLowerCase();  // 转换为小写
  }
}
