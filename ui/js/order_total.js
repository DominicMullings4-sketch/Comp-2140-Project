// Used to calculate line totals on orders. ;

function updateTotal() {
    let grandTotal = 0;

    // Each product row in the order form has class="order-line"
    document.querySelectorAll('#order-form .order-line').forEach(function (row) {
        const productBox = row.querySelector('select');
        const quantityBox = row.querySelector('input');

        const chosenOption = productBox.options[productBox.selectedIndex];
        const price = parseFloat(chosenOption.dataset.price || '0');
        const quantity = parseInt(quantityBox.value, 10) || 0;   // empty box counts as 0

        const lineTotal = price * quantity;
        row.querySelector('.line-total').textContent = lineTotal > 0 ? formatMoney(lineTotal) : '';
        grandTotal += lineTotal;
    });

    document.getElementById('grand-total').textContent = formatMoney(grandTotal);
}

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
