(function(angular, $, _, undefined) {

  // Cache list of actions
  var actions = [];
  // Cache list of fields
  var fields = [];

  angular.module('api4').config(function($routeProvider) {
      $routeProvider.when('/api4/:api4entity?/:api4action?', {
        controller: 'Api4Explorer',
        templateUrl: '~/api4/Explorer.html',
        reloadOnSearch: false
      });
    }
  );

  angular.module('api4').controller('Api4Explorer', function($scope, $routeParams, $location, $timeout, crmUiHelp, crmApi4) {
    var ts = $scope.ts = CRM.ts('api4');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/Api4/Explorer'});
    $scope.entities = CRM.vars.api4.entities;
    $scope.operators = arrayToSelect2(CRM.vars.api4.operators);
    $scope.actions = actions;
    $scope.fields = fields;
    $scope.availableParams = [];
    $scope.params = {};
    var fancyParams = $scope.fancyParams = ['where', 'values', 'orderBy'];
    $scope.entity = $routeParams.api4entity;
    $scope.result = [];
    $scope.status = 'default';
    $scope.loading = false;
    $scope.controls = {};
    $scope.code = {
      php: '',
      javascript: ''
    };

    function ucfirst(str) {
      return str[0].toUpperCase() + str.slice(1);
    }

    function lcfirst(str) {
      return str[0].toLowerCase() + str.slice(1);
    }

    // Turn a flat array into a select2 array
    function arrayToSelect2(array) {
      var out = [];
      _.each(array, function(item) {
        out.push({id: item, text: item});
      });
      return out;
    }

    // Reformat an existing array of objects for compatabilitiy with select2
    function formatForSelect2(input, container, key) {
      _.each(input, function(item) {
        item.id = item.text = item[key];
        delete(item[key]);
        container.push(item);
      });
    }

    // Get all params that have been set
    function getParams() {
      var params = {};
      _.each($scope.params, function(param, key) {
        if (param != $scope.availableParams[key].default && !(typeof param === 'object' && _.isEmpty(param))) {
          params[key] = param;
        }
      });
      return params;
    }

    function selectAction() {
      $scope.action = $routeParams.api4action;
      if ($scope.action) {
        var actionInfo = _.findWhere(actions, {id: $scope.action});
        _.each(actionInfo.params, function (param, name) {
          var format;
          if (param.type) {
            switch (param.type[0]) {
              case 'int':
              case 'bool':
                format = param.type[0];
                break;

              case 'array':
              case 'object':
                format = 'json';
                break;

              default:
                format = 'raw';
            }
            $scope.$bindToRoute({
              expr: 'params["' + name + '"]',
              param: name,
              format: format,
              deep: name === 'where'
            });
          }
          if (fancyParams.indexOf(name) > -1) {
            $scope.$watch('params.' + name, function(values) {
              // Remove empty values
              _.each(values, function(clause, index) {
                if (!clause[0]) {
                  $scope.params[name].splice(index, 1);
                }
              });
            }, true);
            $scope.$watch('controls.' + name, function(value) {
              var field = value;
              $timeout(function() {
                if (field) {
                  var defaultOp = {orderBy: 'ASC', where: '=', values: ''}[name];
                  if (_.isEmpty($scope.params[name])) {
                    $scope.params[name] = [[field, defaultOp]];
                  } else {
                    $scope.params[name].push([field, defaultOp]);
                  }
                  $scope.controls[name] = null;
                }
              });
            });
          }
        });
        $scope.availableParams = actionInfo.params;
      }
      writeCode();
    }

    function writeCode() {
      var code = {
        php: ts('Select an entity and action'),
        javascript: ''
      },
        entity = $scope.entity,
        action = $scope.action,
        params = getParams();
      if ($scope.entity && $scope.action) {
        // Attempt to pluralize in a grammatically correct-ish way
        var varName = lcfirst(entity) + 's';
        if (entity[entity.length-1] === 'y') {
          varName = varName.slice(0, -2) + 'ies';
        }
        code.javascript = "CRM.api4('" + entity + "', '" + action + "', {";
        _.each(params, function(param, key) {
          code.javascript += "\n  " + key + ': ' + JSON.stringify(param);
          if (key === 'checkPermissions') {
            code.javascript += ' // IGNORED: permissions are always enforced from client-side requests';
          }
        });
        code.javascript += "\n}).done(function(" + varName + ") {\n  // do something with " + varName + " array\n});";
        code.php = '$' + varName + " = \\Civi\\Api4\\" + entity + '::' + action + '()';
        _.each(params, function(param, key) {
          if (fancyParams.indexOf(key) > -1) {
            _.each(param, function(item) {
              var val = '';
              _.each(item, function(it) {
                val += ((val.length ? ', ' : '') + JSON.stringify(it));
              });
              code.php += "\n  ->add" + ucfirst(key).replace(/s$/, '') + '(' + val + ')';
            })
          } else {
            code.php += "\n  ->set" + ucfirst(key) + '(' + JSON.stringify(param) + ')';
          }
        });
        code.php += "\n  ->execute();\nforeach ($" + varName + ' as $' + lcfirst(entity) + ') {\n  // do something\n}';
      }
      $scope.code = code;
    }

    $scope.execute = function() {
      $scope.status = 'warning';
      $scope.loading = true;
      crmApi4($scope.entity, $scope.action, getParams())
        .then(function(data) {
          var meta = {},
            result = JSON.stringify(data, null, 2);
          data.length = 0;
          _.assign(meta, data);
          $scope.loading = false;
          $scope.status = 'success';
          $scope.result = [JSON.stringify(meta).replace('{', '').replace(/}$/, ''), result];
        }, function(data) {
          $scope.loading = false;
          $scope.status = 'danger';
          $scope.result = [JSON.stringify(data, null, 2)];
        });
    };

    if (!$scope.entity) {
      $scope.helpTitle = ts('Help');
      $scope.helpText = [ts('Welcome to the api explorer.'), ts('Select an entity to begin.')];
    } else if (!actions.length) {
      crmApi4({actions: [$scope.entity, 'getActions'], fields: [$scope.entity, 'getFields']})
        .then(function(data) {
          formatForSelect2(data.actions, actions, 'name');
          formatForSelect2(data.fields, fields, 'name');
          selectAction();
        });
    } else {
      selectAction();
    }

    if ($scope.entity) {
      $scope.helpTitle = $scope.entity;
      $scope.helpText = [ts('Select an action')];
    }

    // Update route when changing entity
    $scope.$watch('entity', function(newVal, oldVal) {
      if (oldVal !== newVal) {
        // Flush actions cache to re-fetch for new entity
        actions = [];
        fields = [];
        $location.url('/api4/' + newVal);
      }
    });

    // Update route when changing actions
    $scope.$watch('action', function(newVal, oldVal) {
      if ($scope.entity && $routeParams.api4action !== newVal && !_.isUndefined(newVal)) {
        $location.url('/api4/' + $scope.entity + '/' + newVal);
      } else if (newVal) {
        $scope.helpTitle = $scope.entity + '::' + newVal;
        $scope.helpText = [_.findWhere(actions, {id: newVal}).description];
      }
    });

    $scope.$watch('params', writeCode, true);
    writeCode();

  });

})(angular, CRM.$, CRM._);
