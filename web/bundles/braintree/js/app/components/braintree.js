require.config({
  paths: {
    braintree: 'https://js.braintreegateway.com/js/braintree-2.31.0.min'
  }
});

require(['braintree'], function (braintree) {
    braintree.setup("sandbox_xbhxzdjx_n2w2d522qmdbjjv9", "custom", {
        id: "my-sample-form",
        hostedFields: {
          number: {
            selector: "#card-number",
            placeholder: '4111 1111 1111 1111'
          },
          cvv: {
            selector: "#cvv",
            placeholder: '123'
          },
          expirationDate: {
            selector: "#expiration-date",
            placeholder: 'MM/YYYY'
          },
          styles: {
              'input': {
                'color': '#1A41A4',
                'font-family': 'Helvetica, sans-serif',
                'font-size': '16pt'
              },
              'input.invalid': {
                'color': 'red'
              }
            }         
        }
      });
});