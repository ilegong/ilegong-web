(function (window, angular) {
  angular.module('module.filters', [])
    .filter('unsafe', unsafe)
    .filter('thumb', thumb)

  function unsafse($sce) {
    return function (val) {
      return $sce.trustAsHtml(val);
    };
  }

  function thumb() {
    return function (input, type) {
      input = input || '';
      if (input.indexOf('/s/') >= 0 || input.indexOf('/m/') >= 0) {
        return input;
      }

      var thumb_type = 's';
      if (type == 'm') {
        thumb_type = 'm';
      }

      if (input.indexOf('avatar/') >= 0) {
        return input.replace('/avatar/', '/avatar/' + thumb_type + '/');
      }
      if (input.indexOf('/images/') >= 0) {
        return input.replace('/images/', '/images/' + thumb_type + '/');
      }

      return input;
    };
  }
})(window, window.angular);