(function () {
  function ajax ({
    method = 'GET',
    url,
    data = {},               // 默认空数据
    token = null,            // 默认无 token
    headers = {},            // 默认无额外 headers
    contentType = '', // 默认 content-type 为 JSON
    dataType = 'json',       // 默认返回数据类型为 JSON
    timeout = 30000,          // 默认 30 秒超时
    processData = true,      // 默认处理数据
    cache = true,            // 默认启用缓存
    crossDomain = false,     // 默认不开启跨域
    withCredentials = false, // 默认不发送凭证
    async = true,            // 默认异步请求
    success = function () { }, // 默认空成功回调
    error = function () { },   // 默认空错误回调
    beforeSend = function () { },  // 默认空 beforeSend 回调
    complete = function () { },    // 默认空 complete 回调
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
      data: (contentType === 'application/json' || dataType === 'application/json') ? JSON.stringify(data) : data,
      async: async,
      dataType: dataType,
      processData: processData,
      cache: cache,
      crossDomain: crossDomain,
      timeout: timeout,
      xhrFields: {
        withCredentials: withCredentials
      },
      beforeSend: function (xhr) {
        beforeSend(xhr);  // 自定义 beforeSend 回调
      },
      success: function (response) {
        success(response);  // 自定义成功回调
      },
      error: function (xhr, status, errorMsg) {
        const errorMessage = xhr.responseJSON && xhr.responseJSON.message
          ? xhr.responseJSON.message
          : errorMsg;
        error(xhr, status, errorMessage);  // 自定义错误回调
      },
      complete: function (xhr, status) {
        complete(xhr, status);  // 自定义 complete 回调
      }
    });
  }

  window.ajax = ajax;
})();