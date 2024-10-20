为了同时满足具备所有功能特性和调用时极简的要求，我们可以将这些选项配置成默认值，并允许调用时进行必要的覆盖。这种方法确保了在大多数情况下可以用默认配置来简化调用，而在需要时仍可以灵活扩展。

### 自定义 `ajax` 函数（支持默认值 + 极简调用）

我们可以通过使用默认参数实现这一点，使得你只需传入最必要的信息，而其他配置项如请求方法、超时、跨域处理等，都有默认值。这将极大简化常见场景下的调用，并为复杂场景保留灵活性。

### 改进版的 `ajax` 函数

```javascript
function ajax({
  method = 'GET',
  url,
  data = {},               // 默认空数据
  token = null,            // 默认无 token
  headers = {},            // 默认无额外 headers
  contentType = 'application/json', // 默认 content-type 为 JSON
  dataType = 'json',       // 默认返回数据类型为 JSON
  timeout = 5000,          // 默认 5 秒超时
  processData = true,      // 默认处理数据
  cache = true,            // 默认启用缓存
  crossDomain = false,     // 默认不开启跨域
  withCredentials = false, // 默认不发送凭证
  async = true,            // 默认异步请求
  success = function() {}, // 默认空成功回调
  error = function() {},   // 默认空错误回调
  beforeSend = function() {},  // 默认空 beforeSend 回调
  complete = function() {},    // 默认空 complete 回调
} = {}) {
  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  $.ajax({
    type: method,
    url: url,
    headers: {
      'Content-Type': contentType,
      ...headers,
    },
    data: contentType === 'application/json' ? JSON.stringify(data) : data,
    async: async,
    dataType: dataType,
    processData: processData,
    cache: cache,
    crossDomain: crossDomain,
    timeout: timeout,
    xhrFields: {
      withCredentials: withCredentials
    },
    beforeSend: function(xhr) {
      beforeSend(xhr);  // 自定义 beforeSend 回调
    },
    success: function(response) {
      success(response);  // 自定义成功回调
    },
    error: function(xhr, status, errorMsg) {
      const errorMessage = xhr.responseJSON && xhr.responseJSON.message 
        ? xhr.responseJSON.message 
        : errorMsg;
      error(xhr, status, errorMessage);  // 自定义错误回调
    },
    complete: function(xhr, status) {
      complete(xhr, status);  // 自定义 complete 回调
    }
  });
}
```

### 极简调用示例

1. **最简调用**（只需要 `url`）：

```javascript
ajax({
  url: '/api/endpoint'
});
```

2. **带数据的 POST 请求**（简化常见用法）：

```javascript
ajax({
  method: 'POST',
  url: '/api/form/submit',
  data: { name: 'John', age: 30 }
});
```

3. **带 `token` 和自定义成功处理的请求**：

```javascript
ajax({
  method: 'POST',
  url: '/api/form/submit',
  data: { name: 'John', age: 30 },
  token: 'your-jwt-token-here',
  success: function(response) {
    console.log('表单提交成功: ', response);
  },
  error: function(xhr, status, errorMessage) {
    console.error('提交失败: ' + errorMessage);
  }
});
```

4. **请求 HTML 响应**：

```javascript
ajax({
  url: '/page/load',
  dataType: 'html',
  success: function(htmlContent) {
    $('#content').html(htmlContent);
  }
});
```

5. **带跨域和凭证的请求**：

```javascript
ajax({
  url: '/api/data',
  crossDomain: true,
  withCredentials: true,
  success: function(response) {
    console.log('跨域请求成功:', response);
  }
});
```

### 可扩展的特性

- 默认 `GET` 请求和 `JSON` 数据处理，支持极简调用。
- 请求头可以自定义并自动处理 `Authorization`。
- 错误信息处理优先使用 `xhr.responseJSON.message`，确保兼容错误信息返回。
- 支持 `token`、`跨域`、`同步或异步请求`、`数据类型` 等高级功能，默认设置合理但可灵活覆盖。
- 成功、错误、请求前和完成后的钩子函数，支持自定义处理逻辑。

### 总结

通过这种设计，你可以轻松应对常见场景，比如发送表单数据、处理 JSON 或 HTML 响应。而在需要时，仍能覆盖默认设置，处理跨域、授权、或更复杂的请求逻辑。
