Pyshuo = {};
Pyshuo.share = {};
Pyshuo.share.utils = {
  init: function () {
    this.bottomNavDom = $('#share-bottom-nav');
  },
  checkHasUnreadInfo: function () {
    $.getJSON('/share_opt/check_opt_has_new', function (data) {
      //reset bottom nav
    });
  },
  saveToLocalStorage: function (key, value) {
    if (this.storage) {
      this.storage[key] = value;
    }
  },
  readFromLocalStorage: function (key) {
    if (this.storage) {
      return this.storage.getItem(key);
    }
  },
  initLocalStorage: function () {
    if (window.localStorage) {
      if (!this.storage) {
        this.storage = window.localStorage;
      }
    }
  }
};
