// Feature 4.3: shows the order's grand total while staff enter it.  Owner: ______ (same as Feature 4)
// This is the only JavaScript in Track-It. The server (OrderService) still checks
// everything and works out the real total; this is just for the person typing.
//
// Plan:
//   1. Each product <option> carries its price:  <option value="3" data-price="1500.00">
//   2. Whenever a product or quantity changes, go through every line:
//        price = the selected option's data-price,  quantity = the quantity box's value
//        total = total + price * quantity
//   3. Put the result in an element like <span id="grand-total"></span>,
//      formatted as money:  total.toLocaleString('en-JM', { style: 'currency', currency: 'JMD' })
//
// Hint: listen on the whole form, so lines added later are included automatically:
//   document.querySelector('#order-form').addEventListener('input', updateTotal);

function updateTotal() {
    // TODO (Feature 4)
}
