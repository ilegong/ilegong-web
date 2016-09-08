(function (window, angular) {
    angular.module('module.directives', [])
        .directive('fallbackSrc', fallbackSrc)
        .directive('stringToNumber', stringToNumber)
        .directive('readMore', readMore)
        .directive('lazySrc', ['$window', '$document', lazySrc]);

    function fallbackSrc() {
        return {
            link: function postLink(scope, iElement, iAttrs) {
                iElement.bind('error', function () {
                    var oldSrc = angular.element(this).attr("src");
                    if (oldSrc != iAttrs.fallbackSrc) {
                        angular.element(this).attr("src", iAttrs.fallbackSrc);
                    }
                });
            }
        };
    }

    function stringToNumber() {
        return {
            require: 'ngModel',
            link: function (scope, element, attrs, ngModel) {
                ngModel.$parsers.push(function (value) {
                    if (value) {
                        return '' + value;
                    }
                    return '';
                });
                ngModel.$formatters.push(function (value) {
                    if (value) {
                        return parseFloat(value);
                    }
                    return null;
                });
            }
        };
    }

    function lazySrc($window, $document) {
        var doc = $document[0],
            body = doc.body,
            win = $window,
            $win = angular.element(win),
            uid = 0,
            elements = {};

        function getUid(el) {
            return el.__uid || (el.__uid = '' + ++uid);
        }

        function getWindowOffset() {
            var t,
                pageXOffset = (typeof win.pageXOffset == 'number') ? win.pageXOffset : (((t = doc.documentElement) || (t = body.parentNode)) && typeof t.ScrollLeft == 'number' ? t : body).ScrollLeft,
                pageYOffset = (typeof win.pageYOffset == 'number') ? win.pageYOffset : (((t = doc.documentElement) || (t = body.parentNode)) && typeof t.ScrollTop == 'number' ? t : body).ScrollTop;
            return {
                offsetX: pageXOffset,
                offsetY: pageYOffset
            };
        }

        function isVisible(iElement) {
            var elem = iElement[0],
                elemRect = elem.getBoundingClientRect(),
                windowOffset = getWindowOffset(),
                winOffsetX = windowOffset.offsetX,
                winOffsetY = windowOffset.offsetY,
                elemWidth = elemRect.width,
                elemHeight = elemRect.height,
                elemOffsetX = elemRect.left + winOffsetX,
                elemOffsetY = elemRect.top + winOffsetY,
                viewWidth = Math.max(doc.documentElement.clientWidth, win.innerWidth || 0),
                viewHeight = Math.max(doc.documentElement.clientHeight, win.innerHeight || 0),
                xVisible,
                yVisible;
            if (elem.style.display == "none") {
                return false;
            }

            if (elemOffsetY <= winOffsetY) {
                if (elemOffsetY + elemHeight >= winOffsetY) {
                    yVisible = true;
                }
            } else if (elemOffsetY >= winOffsetY) {
                if (elemOffsetY <= winOffsetY + viewHeight) {
                    yVisible = true;
                }
            }

            if (elemOffsetX <= winOffsetX) {
                if (elemOffsetX + elemWidth >= winOffsetX) {
                    xVisible = true;
                }
            } else if (elemOffsetX >= winOffsetX) {
                if (elemOffsetX <= winOffsetX + viewWidth) {
                    xVisible = true;
                }
            }

            return xVisible && yVisible;
        };

        function checkImage() {
            Object.keys(elements).forEach(function (key) {
                var obj = elements[key],
                    iElement = obj.iElement,
                    $scope = obj.$scope;
                if (isVisible(iElement)) {
                    iElement.attr('src', $scope.lazySrc);
                }
            });
        }

        $win.bind('scroll', checkImage);
        $win.bind('resize', checkImage);

        function onLoad() {
            var $el = angular.element(this),
                uid = getUid($el);

            $el.css('opacity', 1);

            if (elements.hasOwnProperty(uid)) {
                delete elements[uid];
            }
        }

        return {
            restrict: 'A',
            scope: {
                lazySrc: '@'
            },
            link: function ($scope, iElement) {

                iElement.bind('load', onLoad);

                $scope.$watch('lazySrc', function () {
                    if (isVisible(iElement)) {
                        if (!iElement.attr('src')) {
                            iElement.attr('src', $scope.lazySrc);
                        }
                    } else {
                        var uid = getUid(iElement);
                        iElement.css({
                            'background-color': '#fff',
                            'opacity': 0,
                            '-webkit-transition': 'opacity 1s',
                            'transition': 'opacity 1s'
                        });
                        elements[uid] = {
                            iElement: iElement,
                            $scope: $scope
                        };
                    }
                });

                $scope.$on('$destroy', function () {
                    iElement.unbind('load');
                });
            }
        };
    }

    function readMore() {
        return {
            restrict: 'A',
            transclude: true,
            replace: true,
            scope: {
                moreText: '@',
                lessText: '@',
                words: '@',
                ellipsis: '@',
                char: '@',
                limit: '@',
                content: '@'
            },
            link: function (scope, elem, attr, ctrl, transclude) {
                var moreText = angular.isUndefined(scope.moreText) ? ' <a class="read-more">Read More...</a>' : ' <a class="read-more">' + scope.moreText + '</a>',
                    lessText = angular.isUndefined(scope.lessText) ? ' <a class="read-less">Less ^</a>' : ' <a class="read-less">' + scope.lessText + '</a>',
                    ellipsis = angular.isUndefined(scope.ellipsis) ? '' : scope.ellipsis,
                    limit = angular.isUndefined(scope.limit) ? 150 : scope.limit;
                attr.$observe('content', function (str) {
                    readmore(str);
                });
                transclude(scope.$parent, function (clone, scope) {
                    readmore(clone.text().trim());
                });
                function readmore(text) {
                    var text = text,
                        orig = text,
                        regex = /\s+/gi,
                        charCount = text.length,
                        wordCount = text.trim().replace(regex, ' ').split(' ').length,
                        countBy = 'char',
                        count = charCount,
                        foundWords = [],
                        markup = text,
                        more = '';
                    if (!angular.isUndefined(attr.words)) {
                        countBy = 'words';
                        count = wordCount;
                    }
                    if (countBy === 'words') { // Count words
                        foundWords = text.split(/\s+/);
                        if (foundWords.length > limit) {
                            text = foundWords.slice(0, limit).join(' ') + ellipsis;
                            more = foundWords.slice(limit, count).join(' ');
                            markup = '<div class="less-text">' + text + moreText + '</div><div class="more-text">' + orig + lessText + '</div>';
                        }
                    } else { // Count characters
                        if (count > limit) {
                            text = orig.slice(0, limit) + ellipsis;
                            text = text.replace(/<\/?[^>]+(>|$)/g, "");
                            more = orig.slice(limit, count);
                            markup = '<div class="less-text">' + text + moreText + '</div><div class="more-text">' + orig + lessText + '</div>';
                        }
                    }
                    elem.append(markup);
                    angular.element(document.getElementsByClassName('read-more')[0]).bind('click', function () {
                        document.getElementsByClassName('less-text')[0].style.display = 'none';
                        document.getElementsByClassName('read-more')[0].style.display = 'none';
                        document.getElementsByClassName('more-text')[0].style.display = 'block';
                        document.getElementsByClassName('read-less')[0].style.display = 'block';
                        videoIFrame = document.getElementById('video');
                        if (videoIFrame) {
                            video.style.dislay = 'block';
                        }
                    });
                    angular.element(document.getElementsByClassName('read-less')[0]).bind('click', function () {
                        document.getElementsByClassName('less-text')[0].style.display = 'block';
                        document.getElementsByClassName('read-more')[0].style.display = 'block';
                        document.getElementsByClassName('more-text')[0].style.display = 'none';
                        document.getElementsByClassName('read-less')[0].style.display = 'none';
                        videoIFrame = document.getElementById('video');
                        if (videoIFrame) {
                            video.style.dislay = 'none';
                        }
                    });
                }
            }
        };
    }

})(window, window.angular);