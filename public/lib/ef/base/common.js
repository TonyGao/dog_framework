class Common {
  static forceReloadJS(srcUrlContains) {
    $.each($('script:empty[src*="' + srcUrlContains + '"]'), function(index, el) {
      let oldSrc = $(el).attr('src');
      const cleanUrl = oldSrc.replace(/\?.*/, "");
      let t = +new Date();
      let newSrc = cleanUrl + '?' + t;
      $(el).remove();
      $('<script/>').attr('src', newSrc).appendTo('head');
    });
  }
}
