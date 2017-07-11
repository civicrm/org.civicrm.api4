(function($, _) {
  CRM.api4 = function(entity, action, params) {
    var deferred = $.Deferred();
    $.post(CRM.url('civicrm/ajax/api4/' + entity + '/' + action), {
      params: JSON.stringify(params)
    })
      .done(function(data) {
        var result = data.values || [];
        delete(data.values);
        // result is an array, but in js, an array is also an object
        // Assign all the metadata properties to it, mirroring the results arrayObject in php
        _.assign(result, data);
        deferred.resolve(result);
      })
      .fail(function(data) {
        deferred.reject(data.responseJSON);
      });

    return deferred;
  };
})(CRM.$, CRM._);