(function(angular, $, _) {

  // Cache list of actions
  var actions = [];

  function ucfirst(str) {
    return str[0].toUpperCase() + str.slice(1);
  }

  function lcfirst(str) {
    return str[0].toLowerCase() + str.slice(1);
  }

  angular.module('api4').config(function($routeProvider) {
      $routeProvider.when('/api4/:api4entity?/:api4action?', {
        controller: 'Api4Explorer',
        templateUrl: '~/api4/Explorer.html',
        reloadOnSearch: false
      });
    }
  );

  angular.module('api4').controller('Api4Explorer', function($scope, $routeParams, $location, crmUiHelp, crmApi4) {
    var ts = $scope.ts = CRM.ts('api4');
    var hs = $scope.hs = crmUiHelp({file: 'CRM/Api4/Explorer'});
    $scope.entities = CRM.vars.api4.entities;
    $scope.actions = actions;
    $scope.availableParams = [];
    $scope.params = {};
    $scope.entity = $routeParams.api4entity;
    $scope.result = [];
    $scope.status = 'default';
    $scope.loading = false;
    $scope.code = {
      php: '',
      javascript: ''
    };

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
              format: format
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
        params = $scope.params;
      if ($scope.entity && $scope.action) {
        var varName = lcfirst(entity) + 's';
        code.javascript = "CRM.api4('" + entity + "', '" + action + "', " +
            JSON.stringify(params, null, 2) +
            ").done(function(" + varName + ") {\n  // do something with " + varName + " array\n});";
        code.php = '$' + varName + " = \\Civi\\Api4\\" + entity + '::' + action + '()';
        _.each(params, function(param, key) {
          code.php += "\n  ->set" + ucfirst(key) + '(' + param + ')';
        });
        code.php += "\n  ->execute();";
      }
      $scope.code = code;
    }

    $scope.execute = function() {
      $scope.status = 'warning';
      $scope.loading = true;
      crmApi4($scope.entity, $scope.action, $scope.params)
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
      crmApi4($scope.entity, 'getActions')
        .then(function(data) {
          _.each(data, function(action) {
            action.id = action.text = action.name;
            delete(action.name);
            actions.push(action);
          });
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

    $scope.$watchCollection('params', writeCode);
    writeCode();

  });

})(angular, CRM.$, CRM._);
