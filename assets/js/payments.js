let dataTable;

document.addEventListener("DOMContentLoaded", function () {
  

    fetchPayments();
    let currentPaymentData = null;

    function openEditModal(data) {
        currentPaymentData = data;

        // Fill form fields
        document.getElementById('invoiceNo').value = data.invoice_no;
        document.getElementById('customername').value = data.customer_name;
        document.getElementById('dueamount').value = data.due;
        document.getElementById('payment').value = '';
        document.getElementById('remaining').value = data.due;
        document.getElementById('nextpaymentdate').value = '';

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
        modal.show();
    }

    window.openEditModal = openEditModal;

    // Calculate remaining on payment input
    document.getElementById('payment').addEventListener('input', () => {
        const due = parseFloat(document.getElementById('dueamount').value);
        const payment = parseFloat(document.getElementById('payment').value) || 0;
        const remaining = Math.max(due - payment, 0);
        document.getElementById('remaining').value = remaining;
    });

    // Save edited payment
    document.getElementById('saveEditPaymentBtn').addEventListener('click', () => {
        const invoiceNo = document.getElementById('invoiceNo').value.trim();
        const customerName = document.getElementById('customername').value.trim();
        const due = parseFloat(document.getElementById('dueamount').value);
        const paid = parseFloat(document.getElementById('payment').value) || 0;
        const remaining = parseFloat(document.getElementById('remaining').value);
        const nextPaymentDate = document.getElementById('nextpaymentdate').value;

        if (!invoiceNo || !customerName || isNaN(due) || isNaN(paid)) {
            alert('Please fill in all required fields correctly.');
            return;
        }

        if (paid > due) {
            alert("Payment amount cannot exceed due amount.");
            return;
        }

        if (remaining === 0) {
            // Delete if fully paid
            fetch('api/payments_handler.php', {
    method: 'DELETE',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: `invoice_no=${encodeURIComponent(invoiceNo)}`
})
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        alert('Payment cleared and record deleted.');
                        // Close modal and reload table
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
                        if (modal) modal.hide();
                        fetchPayments();
                    } else {
                        alert('Failed to delete: ' + res.message);
                    }
                });
        } else {
            // Partial update
            fetch('api/payments_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    invoice_no: invoiceNo,
                    customer_name: customerName,
                    customer_phone: currentPaymentData.customer_phone,
                    total: due,
                    paid: paid,
                    due: remaining,
                    payment_method: currentPaymentData.payment_method,
                    next_payment_date: nextPaymentDate
                })
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Server error ${response.status}: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        console.log('Success:', data);
                        alert("Payment updated successfully!");
                        // ✅ Close modal and reload table
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addPaymentModal'));
                        if (modal) modal.hide();
                        fetchPayments();
                    } else {
                        alert("Update failed: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert("An error occurred while updating payment. Check console for details.");
                });
        }
    });

    // Fetch table data
    function fetchPayments() {
    fetch("api/payments_handler.php?type=list")
        .then(res => res.json())
        .then(data => {
            // Destroy previous DataTable if already initialized
            if ($.fn.DataTable.isDataTable('#datatable-buttons')) {
                $('#datatable-buttons').DataTable().destroy();
            }

            const tbody = document.getElementById("invoice-table-body");
            tbody.innerHTML = "";

            data.data.forEach((row, index) => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.invoice_no}</td>
                    <td>${row.customer_name}</td>
                    <td>${row.customer_phone}</td>
                    <td>${row.transaction_time}</td>
                    <td>${row.items}</td>
                    <td>${row.total}</td>
                    <td>${row.paid}</td>
                    <td>${row.due}</td>
                    <td>${row.next_payment_date}</td>
                    <td>${row.payment_method}</td>
                    <td>
                        <i class="fas fa-edit" style="cursor:pointer;" onclick='openEditModal(${JSON.stringify(row)})'></i>
                    </td>`;
                tbody.appendChild(tr);
            });

            // Re-initialize DataTable after populating data
            dataTable = $('#datatable-buttons').DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'pdf'],
                responsive: true,
                scrollX: true
            });
        });
}

});

