var csrf = {
  name: $('#csrf').attr('name'),
  hash: $('#csrf').val()
};
var ajax = {
  request: {
    form: function (form, url, method, redirect) {
      var input = form.find('input, textarea, button');
      var data = form.serialize();

      var request = $.ajax({
        url: url,
        data: data,
        method: method,
        type: "JSON"
      });

      inputs.prop('disabled', false);

      request.done(function (response) {
        // Parse and debug server response
        console.log(response);
        response = JSON.parse(response);
        console.log(response);

        // Update the anti-CSRF hash
        csrf.hash = response.hash;
        $('#csrf').val(response.hash);

        // Optionally redirect if the request succeeded
        if (response.success) {
          console.log(response.message);

          // Only redirect if a redirect URL was specified
          if (redirect != null) {
            window.location.replace(redirect);
          }
        } else {
          console.error(response.message);
        }
      });

      request.fail(function (message) {
        console.error(message);
      });

      request.always(function () {
        inputs.prop('disabled', false);
      });
    },
    markup: function () {

    }
  }
};
