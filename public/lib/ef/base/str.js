class Str {
  // 在特定数组中进行模糊查询
  // [{lowerText: 'beijing', orginText: 'Beijing', id: '909575156'}]
  static searchArr(arr, keywords) {
    let keywordsArr;
    if (keywords.indexOf(' ') >= 0) {
      keywordsArr = _.uniqWith(_.split(keywords, ' ').filter(n => n));
    }
    let result = [];
    for (let i = 0; i < arr.length; i++) {
      if (keywords.indexOf(' ') >= 0) {
        $.each(keywordsArr, function(idx, ele) {
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
  static toCamelCase(str) {
		return str.replace(/(~\w)/g, function(match) {
			return match.charAt(1).toUpperCase();
		});
  }

  /**
   * 将首字母进行小写化
   * @param {*} str 
   * @returns 
   */
  static firstLetterToLowerCase(str) {
    return str.charAt(0).toLowerCase() + str.slice(1);
  }
}
