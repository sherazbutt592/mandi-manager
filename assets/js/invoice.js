document.addEventListener("DOMContentLoaded", () => {
  const invoiceForm = document.getElementById("invoiceForm");
  const invoiceModalEl = document.getElementById("invoiceModal");
  const receiptModal = new bootstrap.Modal(document.getElementById("receiptModal"));
  const invNoField = document.getElementById("invno");
  const dateField = document.getElementById("invdate");
  const printBtn = document.getElementById("tm_print_btn");

printBtn.addEventListener("click", () => {
  const printContents = document.getElementById("tm_download_section").innerHTML;

  const styles = `
    <style>
     @page {
    margin: 0;
    size: auto;
  }

  body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background: #f8f8f8;
  }
      #receipt-section {
        position: static;
        top: auto;
        left: auto;
        transform: none;
        margin: 0 auto;
        max-width: 100%;
      }

      .tm_pos_invoice_wrap {
        max-width: 320px;
        margin: auto;
        margin-top: 0px;
        padding: 30px 20px;
        background-color: #fff;
      }

      .tm_pos_company_logo {
        display: flex;
        justify-content: center;
        margin-bottom: 7px;
      }

      .tm_pos_company_logo img {
        vertical-align: middle;
        border: 0;
        max-width: 100%;
        height: auto;
        max-height: 45px;
      }

      .tm_pos_invoice_top {
        text-align: center;
        margin-bottom: 18px;
      }

      .tm_pos_invoice_heading {
        display: flex;
        justify-content: center;
        position: relative;
        text-transform: uppercase;
        font-size: 12px;
        font-weight: 500;
        margin: 10px 0;
      }

      .tm_pos_invoice_heading:before {
        content: '';
        position: absolute;
        height: 0;
        width: 100%;
        left: 0;
        top: 46%;
        border-top: 1px dashed #666;
      }

      .tm_pos_invoice_heading span {
        display: inline-flex;
        padding: 0 5px;
        background-color: #fff;
        z-index: 1;
        font-weight: 500;
        position: relative;
      }

      .tm_list.tm_style1 {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-wrap: wrap;
      }

      .tm_list.tm_style1 li {
        display: flex;
        width: 70%;
        font-size: 12px;
        line-height: 1.2em;
        margin-bottom: 7px;
      }

      .text-right {
        text-align: right;
        justify-content: flex;
      }

      .tm_list_title {
        color: #111;
        margin-right: 4px;
        font-weight: 500;
      }

      .tm_pos_invoice_table {
        width: 100%;
        margin-top: 10px;
        line-height: 1.3em;
      }

      .tm_pos_invoice_table thead th {
        font-weight: 500;
        color: #111;
        text-align: left;
        padding: 8px 3px;
        border-top: 1px dashed #666;
        border-bottom: 1px dashed #666;
      }

      .tm_pos_invoice_table td {
        padding: 4px;
      }

      .tm_pos_invoice_table tbody tr:first-child td {
        padding-top: 10px;
      }

      .tm_pos_invoice_table tbody tr:last-child td {
        padding-bottom: 10px;
        border-bottom: 1px dashed #666;
      }

      .tm_pos_invoice_table th:last-child,
      .tm_pos_invoice_table td:last-child {
        text-align: right;
        padding-right: 0;
      }

      .tm_pos_invoice_table th:first-child,
      .tm_pos_invoice_table td:first-child {
        padding-left: 0;
      }

      .tm_pos_invoice_table tr {
        vertical-align: baseline;
      }

      .tm_bill_list {
        list-style: none;
        margin: 0;
        padding: 12px 0;
        border-bottom: 1px dashed #666;
      }

      .tm_bill_list_in {
        display: flex;
        text-align: right;
        justify-content: flex-end;
        padding: 3px 0;
      }

      .tm_bill_title {
        padding-right: 20px;
      }

      .tm_bill_value {
        width: 90px;
      }

      .tm_bill_value.tm_bill_focus,
      .tm_bill_title.tm_bill_focus {
        font-weight: 500;
        color: #111;
      }

      .tm_pos_invoice_footer {
        text-align: center;
        margin-top: 20px;
      }

      .tm_pos_company_name {
        font-weight: 500;
        color: #111;
        font-size: 13px;
        line-height: 1.4em;
      }

      .tm_pos_invoice_wrap {
        box-sizing: border-box;
      }
    </style>
  `;

  const printWindow = window.open('', '', 'width=800,height=600');
  printWindow.document.write(`
    <html>
      <head>
        <title>Print Receipt</title>
        ${styles}
      </head>
      <body><div style="zoom: 0.9; transform: scale(0.9); transform-origin: top center;">
    ${printContents}
  </div></body>
    </html>
  `);

  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
});

  const DateUtils = {
    generateInvoiceNumber() {
      return "INV-" + new Date().getTime();
    },

    formatTime12Hour(time24) {
      const [h, m, s] = time24.split(":");
      let hour = parseInt(h, 10);
      const ampm = hour >= 12 ? "PM" : "AM";
      hour = hour % 12 || 12;
      return `${hour}:${m}:${s} ${ampm}`;
    },

    formatDateTimeMySQL(date) {
      const pad = num => String(num).padStart(2, "0");
      return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
    }
  };

  const API = {
    saveInvoice(data) {
      return fetch("api/invoice_handler.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data)
      }).then(res => res.json());
    },

    fetchCustomers() {
      return fetch("api/invoice_handler.php?type=customers").then(res => res.json());
    },

    fetchProducts() {
      return fetch("api/invoice_handler.php?type=products").then(res => res.json());
    }
  };

  const UI = {
    updateInvoiceFields() {
      invNoField.value = DateUtils.generateInvoiceNumber();
      const now = new Date();
      const yyyy = now.getFullYear();
      const mm = String(now.getMonth() + 1).padStart(2, "0");
      const dd = String(now.getDate()).padStart(2, "0");
      dateField.value = `${yyyy}-${mm}-${dd}`;
    },

    populateCustomerOptions(customers) {
      const select = document.getElementById("customerSelect");
      select.innerHTML = '<option value="">Select Customer</option>' +
        customers.map(c => `<option value="${c.name}">${c.name} (Score: ${c.credit_score})</option>`).join('');
    },

    populateProductOptions(select, products) {
      select.innerHTML = '<option value="">Select Item</option>' +
        products.map(p => `<option value="${p.name}/${p.serial}">${p.name}/${p.serial} - Qty: ${p.quantity}</option>`).join('');
    },

    resetFormAndTable() {
      invoiceForm.reset();
      const tbody = document.getElementById("tableBody");
      const newRow = tbody.rows[0].cloneNode(true);
      newRow.querySelectorAll("input").forEach(input => input.value = "");
      newRow.querySelector("select").selectedIndex = 0;
      tbody.innerHTML = "";
      tbody.appendChild(newRow);
      loadProductOptions(newRow.querySelector("select.particularSelect"));
      UI.updateRowNumbers();
      UI.updateInvoiceFields();
    },

    updateRowNumbers() {
      document.querySelectorAll("#tableBody tr").forEach((tr, idx) => {
        tr.cells[0].textContent = idx + 1;
      });
    },

    calculateTotal() {
      let total = 0;
      document.querySelectorAll(".amount").forEach(el => {
        total += parseFloat(el.value) || 0;
      });
      document.getElementById("total").value = total.toFixed(2);
      const paid = parseFloat(document.getElementById("paid").value) || 0;
      document.getElementById("dueamt").value = (total - paid).toFixed(2);
    },

    populateReceipt({ customerName, invNo, transactionTime, items, paid, dueAmount, paymentMethod, nextPaymentDate }) {
      document.getElementById("receipt-customer").textContent = customerName;
      document.getElementById("receipt-invno").textContent = invNo;
      const [date, time] = transactionTime.split(" ");
      document.getElementById("receipt-date").textContent = `${date} / ${DateUtils.formatTime12Hour(time)}`;

      const receiptItems = document.getElementById("receipt-items");
      receiptItems.innerHTML = "";
      let total = 0;
      items.forEach((item, i) => {
        if (!item?.item?.trim()) return;
        total += parseFloat(item.amount) || 0;
        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${i + 1}</td>
          <td>${item.item}</td>
          <td>${item.rate}</td>
          <td>${item.qty}</td>
          <td>${item.amount}</td>
        `;
        receiptItems.appendChild(tr);
      });

      document.getElementById("receipt-total").textContent = total.toFixed(2);
      document.getElementById("receipt-paid").textContent = paid;
      document.getElementById("receipt-dueamt").textContent = dueAmount;
      document.getElementById("receipt-payment-method").textContent = paymentMethod;
      document.getElementById("receipt-next-payment-date").textContent = nextPaymentDate || "-";

      receiptModal.show();
      bootstrap.Modal.getInstance(invoiceModalEl).hide();
    }
  };

  UI.updateInvoiceFields();

  API.fetchCustomers()
    .then(data => data.success && UI.populateCustomerOptions(data.data));

  document.querySelectorAll("select.particularSelect").forEach(loadProductOptions);

  function loadProductOptions(select) {
    API.fetchProducts().then(data => {
      if (data.success) UI.populateProductOptions(select, data.data);
      else select.innerHTML = '<option value="">Failed to load items</option>';
    });
  }

  invoiceForm.addEventListener("submit", function (event) {
    event.preventDefault();

    const submitBtn = invoiceForm.querySelector("button[type='submit']");
    submitBtn.disabled = true;

    const customerName = invoiceForm.customer.value;
    const invNo = invNoField.value;
    const total = parseFloat(document.getElementById("total").value);
    const paid = parseFloat(invoiceForm.paid.value);
    const dueAmount = parseFloat(document.getElementById("dueamt").value);
    const paymentMethod = invoiceForm.payment_method.value;
    const nextPaymentDate = invoiceForm.next_payment_date.value || null;

    const items = [...document.getElementById("tableBody").rows].map(row => ({
      item: row.querySelector("select[name='particular[]']").value,
      qty: row.querySelector("input[name='qty[]']").value,
      rate: row.querySelector("input[name='rate[]']").value,
      amount: row.querySelector("input[name='amount[]']").value
    })).filter(item => item.item?.trim() && item.qty && item.rate);

    if (!confirm("Are you sure you want to save this invoice?")) {
      submitBtn.disabled = false;
      return;
    }

    const now = new Date();
    const transactionTime = DateUtils.formatDateTimeMySQL(now);

    API.saveInvoice({
      customerName,
      invNo,
      total,
      paid,
      dueAmount,
      paymentMethod,
      nextPaymentDate,
      items,
      transactionTime
    }).then(data => {
      submitBtn.disabled = false;
      if (data.success) {
        alert("Invoice saved successfully!");
        UI.populateReceipt({ customerName, invNo, transactionTime, items, paid, dueAmount, paymentMethod, nextPaymentDate });
        UI.resetFormAndTable();
      } else {
        alert("Error saving invoice: " + data.message);
      }
    }).catch(err => {
      submitBtn.disabled = false;
      alert("Error: " + err.message);
    });
  });

  window.addRow = () => {
    const tbody = document.getElementById("tableBody");
    const newRow = tbody.rows[0].cloneNode(true);
    newRow.querySelectorAll("input").forEach(input => input.value = "");
    loadProductOptions(newRow.querySelector("select.particularSelect"));
    tbody.appendChild(newRow);
    UI.updateRowNumbers();
  };

  window.removeRow = btn => {
    const row = btn.closest("tr");
    const tbody = row.parentElement;
    if (tbody.rows.length > 1) {
      row.remove();
      UI.updateRowNumbers();
      UI.calculateTotal();
    }
  };
  window.calculateTotal = UI.calculateTotal;
  window.calculateAmount = el => {
    const row = el.closest("tr");
    const qty = parseFloat(row.querySelector(".qty").value) || 0;
    const rate = parseFloat(row.querySelector(".rate").value) || 0;
    row.querySelector(".amount").value = (qty * rate).toFixed(2);
    UI.calculateTotal();
  };

  document.getElementById("paid").addEventListener("input", UI.calculateTotal);

  const downloadBtn = document.getElementById("tm_download_btn");
  if (downloadBtn) {
    downloadBtn.addEventListener("click", () => {
      const downloadSection = document.getElementById("tm_download_section");
      html2canvas(downloadSection).then(canvas => {
        const imgData = canvas.toDataURL("image/png");
        const pdf = new jsPDF('p', 'pt', [canvas.width, canvas.height]);
        pdf.addImage(imgData, "PNG", 0, 0, canvas.width, canvas.height);
        pdf.save(`${invNoField.value}-receipt.pdf`);
      });
    });
  }
});
