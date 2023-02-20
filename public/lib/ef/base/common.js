class Common {
  static forceReloadJS(srcUrlContains) {
    $.each($('script:empty[src*="' + srcUrlContains + '"]'), function(index, el) {
      let oldSrc = $(el).attr('src');
      let t = +new Date();
      let newSrc = oldSrc + '?' + t;
      $(el).remove();
      $('<script/>').attr('src', newSrc).appendTo('head');
    });
  }
}
