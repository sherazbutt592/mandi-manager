
document.addEventListener("DOMContentLoaded", () => {
  const resultDiv = document.getElementById("result");
  const invoiceTableBody = document.querySelector("#datatable-buttons tbody");

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
    invoiceTableBody.innerHTML = `<tr><td colspan="11" class="text-center text-muted">${msg}</td></tr>`;
    resultDiv.style.display = "block";
  }
  
let dataTable;
  function fetchInvoices(startDate = "", endDate = "") {
  let url = "api/fetch_invoice_history.php";

  if (startDate && endDate) {
    url += `?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
  }

  showLoading();

  fetch(url)
    .then(res => res.json())
    .then(data => {
      // Destroy previous instance
      if ($.fn.DataTable.isDataTable('#datatable-buttons')) {
        $('#datatable-buttons').DataTable().clear().destroy();
      }

      // Clear the HTML rows
      $('#datatable-buttons tbody').empty();

      // Append new rows
      if (data.success && data.data.length > 0) {
        data.data.forEach((invoice, index) => {
          const formattedDateTime = formatDateTime12Hour(invoice.transaction_time);
          const itemsWithBreaks = invoice.items.replace(/, /g, "<br>");

          $('#datatable-buttons tbody').append(`
            <tr>
              <td>${index + 1}</td>
              <td>${invoice.invoice_number}</td>
              <td>${invoice.customer_id}</td>
              <td>${invoice.customer_name}</td>
              <td>${formattedDateTime}</td>
              <td>${itemsWithBreaks}</td>
              <td>${invoice.total}</td>
              <td>${invoice.paid}</td>
              <td>${invoice.due_amount}</td>
              <td>${invoice.next_payment_date || 'N/A'}</td>
              <td>
                <span class="action_icon">
                  <i class="fas fa-times text-danger" style="cursor:pointer" onclick="removeRecord(${invoice.id}, this)" title="Delete"></i>
                </span>
              </td>
            </tr>
          `);
        });
      }

      // Reinitialize DataTable after updating rows
      dataTable = $('#datatable-buttons').DataTable({
        dom: 'Bfrtip',
        buttons: ['copy', 'pdf'],
        responsive: true,
        scrollX: true
      });

      resultDiv.style.display = "block";
    })
    .catch(err => {
      console.error("Fetch error:", err);
      if (dataTable) dataTable.clear().draw();
      showLoading("Error loading data");
    });
}

  // Initial load
  fetchInvoices();

  // Filtering form
  document.querySelector("#filter-form").addEventListener("submit", e => {
    e.preventDefault();

    const startDate = document.getElementById("start-date").value;
    const endDate = document.getElementById("end-date").value;

    if (!startDate || !endDate) {
      alert("Please select both start and end dates.");
      return;
    }

    if (new Date(startDate) > new Date(endDate)) {
      alert("Start date cannot be after end date.");
      return;
    }

    fetchInvoices(startDate, endDate);
  });

  // Delete handler
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
