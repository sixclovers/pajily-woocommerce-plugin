jQuery( function() {
  const sandbox = jQuery('#woocommerce_pajily_payments_sandbox');
  const publicKey = jQuery('#woocommerce_pajily_payments_public_key');
  const privateKey = jQuery('#woocommerce_pajily_payments_private_key');
  const paymentWalls = jQuery('#woocommerce_pajily_payments_payment_wall_id');
  function getApiEndpoint() {
    return window.develop == 'yes' ? window.developServer : (sandbox.is(':checked') ? window.sandboxServer : window.productionServer);
  }
  function populatePaymentWalls() {
    const publicKeyVal = publicKey.val();
    const privateKeyVal = privateKey.val();
    if(!publicKeyVal || !privateKeyVal || publicKeyVal.length != 32 || privateKeyVal.length != 32) {
      return;
    }
    jQuery.ajax({
      url: getApiEndpoint() + '/v1/transactions/payment-walls/',
      type: 'GET',
      dataType: 'json',
      beforeSend: function(xhr) { xhr.setRequestHeader("Authorization", "Basic " + btoa(publicKeyVal + ":" + privateKeyVal)); },
      success: function(response) {
        paymentWalls.empty();
        response.data.forEach(function(paymentWall) {
          paymentWalls.append('<option value="' + paymentWall.paymentWallId + '">' + paymentWall.name + '</option>');
        });
      }
    });
  }
  publicKey.change(populatePaymentWalls);
  privateKey.change(populatePaymentWalls);
});