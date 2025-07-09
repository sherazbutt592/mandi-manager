document.addEventListener("DOMContentLoaded", () => {
  const resultDiv = document.getElementById("result");
  const invoiceTableBody = document.querySelector("#datatable-buttons tbody");
  let dataTable;

  function formatDateTime12Hour(datetimeStr) {
    if (!datetimeStr) return "N/A";
    const [datePart, timePart] = datetimeStr.split(" ");
    if (!datePart || !timePart) return datetimeStr;

    let [hours, minutes, seconds] = timePart.split(":");
    hours = parseInt(hours, 10);
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12;
    return `${datePart} / ${hours}:${minutes}:${seconds} ${ampm}`;
  }

  function showLoading(msg = "Loading...") {
    invoiceTableBody.innerHTML = `<tr><td colspan="10" class="text-center text-muted">${msg}</td></tr>`;
    resultDiv.style.display = "block";
  }

  function fetchInvoices(startDate = "", endDate = "") {
    let url = "api/fetch_invoice_history.php";
    
    // Ensure both dates are valid before appending them to the URL
    if (startDate && endDate) {
        url += `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    }

    showLoading();

    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (!dataTable) {
                dataTable = $('#datatable-buttons').DataTable({
                    responsive: false,
                    destroy: true,
                    dom: 'Bfrtip',
                    buttons: ['copy', 'pdf', 'print'],
                    columns: [
                        { title: "S.No" },  // Serial number column
                        { title: "Invoice Number" },
                        { title: "Customer ID" },
                        { title: "Customer Name" },
                        { title: "Transaction Time" },
                        { 
                            title: "Items",
                            render: function(data, type, row, meta) {
                                return type === 'display' ? data : data.replace(/<br\s*\/?>/g, ', ');
                            }
                        },
                        { title: "Total" },
                        { title: "Paid" },
                        { title: "Due Amount" },
                        { title: "Next Payment Date" },
                        { 
                            title: "Actions",
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row, meta) {
                                return type === 'display' ? data : '';
                            }
                        }
                    ]
                });
            }

            dataTable.clear();

            if (data.success && data.data.length > 0) {
                data.data.forEach((invoice, index) => {
                    const formattedDateTime = formatDateTime12Hour(invoice.transaction_time);
                    const itemsWithBreaks = invoice.items.replace(/, /g, "<br>");

                    dataTable.row.add([
                        index + 1,  // Serial number: +1 to make it 1-based instead of 0-based
                        invoice.invoice_number,
                        invoice.customer_id,
                        invoice.customer_name,
                        formattedDateTime,
                        itemsWithBreaks,
                        invoice.total,
                        invoice.paid,
                        invoice.due_amount,
                        invoice.next_payment_date || 'N/A',
                        `<span class="action_icon">
                            <i class="fas fa-times text-danger" style="cursor:pointer" onclick="removeRecord(${invoice.id}, this)" title="Delete"></i>
                        </span>`
                    ]);
                });
            }

            dataTable.draw();
            resultDiv.style.display = "block";
        })
        .catch(err => {
            console.error("Fetch error:", err);
            if (dataTable) dataTable.clear().draw();
            showLoading("Error loading data");
        });
}


  fetchInvoices();

 document.querySelector("form").addEventListener("submit", e => {
    e.preventDefault(); // Prevent form submission

    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    // Validate the dates
    if (!startDate || !endDate) {
        alert("Please select both start and end dates.");
        return;
    }

    if (new Date(startDate) > new Date(endDate)) {
        alert("Start date cannot be after end date.");
        return;
    }

    fetchInvoices(startDate, endDate); // Fetch the invoices with the selected date range
});

  window.removeRecord = function (invoiceId, iconElement) {
    if (!confirm("Are you sure you want to delete this invoice?")) return;

    fetch("api/fetch_invoice_history.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "delete", id: invoiceId }),
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert("Invoice deleted successfully!");
          if (dataTable) {
            const row = $(iconElement).closest("tr");
            dataTable.row(row).remove().draw();
          }
        } else {
          alert("Error deleting invoice: " + data.message);
        }
      })
      .catch(err => {
        alert("Network error while deleting invoice.");
        console.error(err);
      });
  };
});
