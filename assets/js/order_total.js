// SR-3.4: shows the order's total while staff enter it. Owner: P4 (same as OrderUI)
// This is the only JavaScript in Track-It. The server (OrderMgmt / Order) still checks
// everything and works out the real total; this is just for the person typing.
//
// Plan:
//   1. Each item <option> carries its price:  <option value="3" data-price="1500.00">
//   2. Whenever an item or quantity changes, go through every line:
//        price = the selected option's data-price,  quantity = the quantity box's value
//        total = total + price * quantity
//   3. Put the result in an element like <span id="grand-total"></span>,
//      formatted as money:  total.toLocaleString('en-JM', { style: 'currency', currency: 'JMD' })
//
// Hint: listen on the whole form, so lines added later are included automatically:
//   document.querySelector('#order-form').addEventListener('input', updateTotal);

function updateTotal() {
    let grandTotal = 0;

    // Each item row in the order form has class="order-line"
    document.querySelectorAll('#order-form .order-line').forEach(function (row) {
        const itemBox = row.querySelector('select');
        const quantityBox = row.querySelector('input');

        // The chosen item's price is on its <option data-price="1500">
        const chosenOption = itemBox.options[itemBox.selectedIndex];
        const price = parseFloat(chosenOption.dataset.price || '0');
        const quantity = parseInt(quantityBox.value, 10) || 0;   // empty box counts as 0

        const lineTotal = price * quantity;
        row.querySelector('.line-total').textContent = lineTotal > 0 ? formatMoney(lineTotal) : '';
        grandTotal += lineTotal;
    });

    document.getElementById('grand-total').textContent = formatMoney(grandTotal);
}

// 1500 becomes $1,500.00 (the same format as PHP's money())
function formatMoney(amount) {
    return '$' + amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Recalculate whenever anything in the form changes, and once when the page opens
// (in case the form was refilled after an error).
const orderForm = document.getElementById('order-form');
if (orderForm) {
    orderForm.addEventListener('input', updateTotal);
    orderForm.addEventListener('change', updateTotal);
    updateTotal();
}
