if (window.wc && window.wc.wcBlocksRegistry)
window.wc.wcBlocksRegistry.registerPaymentMethod({
  name: window.gatewayId,
  title: window.methodTitle,
  description: window.methodDescription,
  gatewayId: window.gatewayId,
  content: React.createElement('div', {}, window.paymentDescription),
  edit: React.createElement('div', {}, ''),
  label: React.createElement('div', {}, window.paymentTitle),
  ariaLabel: window.paymentTitle,
  placeOrderButtonLabel: window.paymentNextButton,
  canMakePayment: function() {return true},
});