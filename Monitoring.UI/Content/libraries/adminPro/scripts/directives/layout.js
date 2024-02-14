webApp.directive("toggleOffCanvas",
[function() {
    return {
        restrict:"A",
        link:function(scope, ele) {
            return ele.on("click", function() {
                return $("#app").toggleClass("on-canvas")
            }
            )
        }
    }
}]);

webApp.directive("slimScroll",
[function() {
    return {
        restrict:"A",
        link:function(scope, ele, attrs) {
            return ele.slimScroll( {
                height: attrs.scrollHeight||"100%"
            }
            )
        }
    }
}]);

webApp.directive("collapseNav",
[function() {
    return {
        restrict:"A",
        link:function(scope, ele) {
            var $a,
            $aRest,
            $app,
            $lists,
            $listsRest,
            $nav,
            $window,
            prevWidth,
            updateClass;
            return $window=$(window),
            $lists=ele.find("ul").parent("li"),
            $a=$lists.children("a"),
            $listsRest=ele.children("li").not($lists),
            $aRest=$listsRest.children("a"),
            $app=$("#app"),
            $nav=$("#nav-container"),
            $a.on("click", function(event) {
                var $parent, $this;
                return $app.hasClass("nav-collapsed-min")||$nav.hasClass("nav-horizontal")&&$window.width()>=768?!1: ($this=$(this), $parent=$this.parent("li"), $lists.not($parent).removeClass("open").find("ul").slideUp(), $parent.toggleClass("open").find("ul").slideToggle(), event.preventDefault())
            }
            ),
            $aRest.on("click", function() {
                return $lists.removeClass("open").find("ul").slideUp()
            }
            ),
            scope.$on("nav:reset", function() {
                return $lists.removeClass("open").find("ul").slideUp()
            }
            ),
            prevWidth=$window.width(),
            updateClass=function() {
                var currentWidth;
                return currentWidth=$window.width(),
                768>currentWidth&&$app.removeClass("nav-collapsed-min"),
                768>prevWidth&&currentWidth>=768&&$nav.hasClass("nav-horizontal")&&$lists.removeClass("open").find("ul").slideUp(),
                prevWidth=currentWidth
            }
            ,
            $window.resize(function() {
                var t;
                return clearTimeout(t), t=setTimeout(updateClass, 300)
            }
            )
        }
    }
}]);

webApp.directive("toggleNavCollapsedMin",
["$rootScope",
function($rootScope) {
    return {
        restrict:"A",
        link:function(scope, ele) {
            var app;
            return app=$("#app"),
            ele.on("click", function(e) {
                return app.hasClass("nav-collapsed-min")?app.removeClass("nav-collapsed-min"): (app.addClass("nav-collapsed-min"), $rootScope.$broadcast("nav:reset")), e.preventDefault()
            }
            )
        }
    }
}]);

webApp.constant('dropdownConfig', {
	  openClass: 'open'
})
.service('dropdownService', ['$document', function($document) {
  var openScope = null;

  this.open = function( dropdownScope ) {
    if ( !openScope ) {
      $document.bind('click', closeDropdown);
      $document.bind('keydown', escapeKeyBind);
    }

    if ( openScope && openScope !== dropdownScope ) {
        openScope.isOpen = false;
    }

    openScope = dropdownScope;
  };

  this.close = function( dropdownScope ) {
    if ( openScope === dropdownScope ) {
      openScope = null;
      $document.unbind('click', closeDropdown);
      $document.unbind('keydown', escapeKeyBind);
    }
  };

  var closeDropdown = function( evt ) {
    // This method may still be called during the same mouse event that
    // unbound this event handler. So check openScope before proceeding.
    if (!openScope) { return; }

    var toggleElement = openScope.getToggleElement();
    if ( evt && toggleElement && toggleElement[0].contains(evt.target) ) {
        return;
    }

    openScope.$apply(function() {
      openScope.isOpen = false;
    });
  };

  var escapeKeyBind = function( evt ) {
    if ( evt.which === 27 ) {
      openScope.focusToggleElement();
      closeDropdown();
    }
  };
}])
.controller('DropdownController', ['$scope', '$attrs', '$parse', 'dropdownConfig', 'dropdownService', '$animate', function($scope, $attrs, $parse, dropdownConfig, dropdownService, $animate) {
  var self = this,
      scope = $scope.$new(), // create a child scope so we are not polluting original one
      openClass = dropdownConfig.openClass,
      getIsOpen,
      setIsOpen = angular.noop,
      toggleInvoker = $attrs.onToggle ? $parse($attrs.onToggle) : angular.noop;

  this.init = function( element ) {
    self.$element = element;

    if ( $attrs.isOpen ) {
      getIsOpen = $parse($attrs.isOpen);
      setIsOpen = getIsOpen.assign;

      $scope.$watch(getIsOpen, function(value) {
        scope.isOpen = !!value;
      });
    }
  };

  this.toggle = function( open ) {
    return scope.isOpen = arguments.length ? !!open : !scope.isOpen;
  };

  // Allow other directives to watch status
  this.isOpen = function() {
    return scope.isOpen;
  };

  scope.getToggleElement = function() {
    return self.toggleElement;
  };

  scope.focusToggleElement = function() {
    if ( self.toggleElement ) {
      self.toggleElement[0].focus();
    }
  };

  scope.$watch('isOpen', function( isOpen, wasOpen ) {
    $animate[isOpen ? 'addClass' : 'removeClass'](self.$element, openClass);

    if ( isOpen ) {
      scope.focusToggleElement();
      dropdownService.open( scope );
    } else {
      dropdownService.close( scope );
    }

    setIsOpen($scope, isOpen);
    if (angular.isDefined(isOpen) && isOpen !== wasOpen) {
      toggleInvoker($scope, { open: !!isOpen });
    }
  });

  $scope.$on('$locationChangeSuccess', function() {
    scope.isOpen = false;
  });

  $scope.$on('$destroy', function() {
    scope.$destroy();
  });
}])

.directive('dropdown', function() {
  return {
    controller: 'DropdownController',
    link: function(scope, element, attrs, dropdownCtrl) {
      dropdownCtrl.init( element );
    }
  };
})

.directive('dropdownToggle', function() {
  return {
    require: '?^dropdown',
    link: function(scope, element, attrs, dropdownCtrl) {
      if ( !dropdownCtrl ) {
        return;
      }

      dropdownCtrl.toggleElement = element;

      var toggleDropdown = function(event) {
        event.preventDefault();

        if ( !element.hasClass('disabled') && !attrs.disabled ) {
          scope.$apply(function() {
            dropdownCtrl.toggle();
          });
        }
      };

      element.bind('click', toggleDropdown);

      // WAI-ARIA
      element.attr({ 'aria-haspopup': true, 'aria-expanded': false });
      scope.$watch(dropdownCtrl.isOpen, function( isOpen ) {
        element.attr('aria-expanded', !!isOpen);
      });

      scope.$on('$destroy', function() {
        element.unbind('click', toggleDropdown);
      });
    }
  };
});

webApp.directive("uiWizardForm", [function() {
    return {
        link:function(scope, ele) {
            return ele.steps()
        }
    }
}]);

webApp.directive("customPage", function() {
    return {
        restrict:"A", controller:["$scope", "$element", "$location", function($scope, $element, $location) {
            var addBg, path;
            return path=function() {
                return $location.path()
            }
            , addBg=function(path) {
                switch($element.removeClass("body-wide body-lock"), path) {
                    case"/404": case"/pages/404": case"/pages/500": case"/pages/signin": case"/pages/signup": case"/pages/forgot-password": return $element.addClass("body-wide");
                    case"/pages/lock-screen": return $element.addClass("body-wide body-lock")
                }
            }
            , addBg($location.path()), $scope.$watch(path, function(newVal, oldVal) {
                return newVal!==oldVal?addBg($location.path()): void 0
            }
            )
        }
        ]
    }
});

webApp.directive("animatectrl", [function() {
    return {
        restrict:"A", link:function(scope, element) {
            element.click(function() {
                var animate=$(this).attr("data-animated");
                $(this).closest(".panel").addClass(animate).delay(1e3).queue(function(next) {
                    $(this).removeClass(animate), next()
                }
                )
            }
            )
        }
    }
}]);

webApp.directive("uiSpinner", [function() {
    return {
        restrict:"A", compile:function(ele) {
            return ele.addClass("ui-spinner"), {
                post:function() {
                    return ele.spinner()
                }
            }
        }
    }
}]);

webApp.directive('datepicker', ['$parse', '$timeout', function ($parse, $timeout) {
    return {
        link: function (scope, element, attrs) {
            element.datetimepicker({
				locale: 'hr',
                format: 'DD.MM.YYYY'
            }).on("dp.change", function (e) {
                var el = $(element).find(attrs.el).first().trigger('input');
                if (e.date) {
                    $parse(attrs.varName).assign(scope, moment(e.date.valueOf()).format('YYYY-MM-DD'));
                    scope.$apply();
                }
                else {
                    $parse(attrs.varName).assign(scope, null);
                    scope.$apply();
                }
                
            });
            attrs.$observe("maxDate", function (value) {
                if (value && moment(value, "DD.MM.YYYY").isValid()) {
                    $timeout(function () {
                        element.data("DateTimePicker").maxDate(moment(value, "DD.MM.YYYY").format('DDMMYYYY'));						
                    }, 0, false);                    
                }
            })
            attrs.$observe("minDate", function (value) {
                if (value && moment(value, "DD.MM.YYYY").isValid()) {
                    $timeout(function () {
                        element.data("DateTimePicker").minDate(moment(value, "DD.MM.YYYY").format('DDMMYYYY'));						
                    }, 0, false);                    
                }
            })
       }
        
    }
}]);

webApp.directive("panelToggle", [function() {
    return {
        restrict:"A", link:function(scope, element) {
            element.click(function() {
                $(this).parent().parent().next().slideToggle("fast"), $(this).toggleClass("fa-chevron-down fa-chevron-up")
            }
            )
        }
    }
}]);

/* ovako definirati globalnu konstantu ili nešto za bootbox standardne optionse
webApp.constant("bootBoxOptions", {
	confirm = {
		message: 'Jeste li sigurni da želite obrisati plan?',
		buttons: {
			warning: {
				label: "Odustani",
				className: "btn waves-effect waves-button waves-float waves-warning",
			},
			success: {
				label: "Obriši",
				className: "btn waves-effect waves-button waves-float waves-info",
				callback: function() { 
					objectService.deleteObjectItemMonitoring(monitoringId).then(function(success){
						$scope.reloadData();
						$ngBootbox.customDialog(optionsSuccess);
					});							
				}
			}
		}
	}
});*/

