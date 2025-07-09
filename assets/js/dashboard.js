var donutChart = null;

$(function () {
  "use strict";

  // Morris Bar Chart - Daily Sales
  if ($("#morris-bar-example").length) {
    $.ajax({
      url: "api/dashboard_alerts.php?type=sales",
      method: "GET",
      dataType: "json",
      success: function (data) {
        Morris.Bar({
          element: "morris-bar-example",
          data: data,
          xkey: "y",
          ykeys: ["a", "b"],
          labels: ["Quantity Sold", "Total Amount (Rs)"],
          barColors: function (row, series, type) {
            const colors = [
              "#20b799", "#f15050", "#9966FF", "#FF9F40", "#4BC0C0",
              "#FF6384", "#8BC34A", "#C0CA33", "#36A2EB", "#D84315"
            ];
            return colors[row.x % colors.length];
          },
          hideHover: "auto",
          resize: true,
          barSizeRatio: 0.5,
          gridLineColor: "#eef0f250"
        });
      },
      error: function (xhr, status, error) {
        console.error("Error loading daily product sales:", error);
      }
    });
  }

  // Morris Donut Chart - Product Quantity Distribution
  if ($("#morris-donut-example").length) {
    $.ajax({
      url: "api/dashboard_alerts.php?action=chart_data",
      method: "GET",
      dataType: "json",
      success: function (chartData) {
        const filteredData = chartData.filter(item => item.value > 0);
        if (typeof donutChart !== "undefined" && donutChart) {
          donutChart.setData(filteredData);
        } else {
          donutChart = Morris.Donut({
            element: "morris-donut-example",
            resize: true,
            backgroundColor: "transparent",
            colors: [
              "#FF6384", "#FFCE56", "#36A2EB", "#4BC0C0", "#9966FF",
              "#FF9F40", "#8BC34A", "#C0CA33", "#D84315", "#AD1457"
            ],
            data: filteredData
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("Error fetching donut chart data:", error);
      }
    });
  }

  // === Dashboard Stats ===

  // Total Customers
  fetch('api/dashboard_alerts.php?type=count')
    .then(response => response.json())
    .then(data => {
      if (data.total_customers !== undefined) {
        document.getElementById('totalCustomersCount').textContent = data.total_customers.toLocaleString();
      } else {
        console.error('Failed to fetch total customers count:', data);
      }
    })
    .catch(error => console.error('Error fetching total customers count:', error));

  // Total Products Quantity
  fetch('api/dashboard_alerts.php?action=total_quantity')
    .then(response => response.json())
    .then(data => {
      if (data.total_quantity !== undefined) {
        document.getElementById('totalProductsCount').textContent = data.total_quantity.toLocaleString();
      } else {
        console.error('Failed to fetch total products count:', data);
      }
    })
    .catch(error => console.error('Error fetching total products count:', error));

  // Monthly Sales
  fetch("api/dashboard_alerts.php?type=monthly_total")
    .then(response => response.json())
    .then(data => {
      document.getElementById('monthly-sales').textContent = data.total_sales;
    })
    .catch(error => console.error('Failed to fetch total sales:', error));

  // Total Due
  fetch('api/dashboard_alerts.php?type=total_due')
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        document.getElementById('total-due').textContent = data.total_due;
      }
    })
    .catch(error => console.error("Error fetching total due:", error));

  // Loan Alerts
  fetch("api/dashboard_alerts.php?type=loan")
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector("#loan-alerts-table tbody");
      tbody.innerHTML = "";

      if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4">No upcoming loan payments.</td></tr>`;
      } else {
        data.forEach(record => {
          const row = `
            <tr class="loan-row" style="cursor: pointer;">
              <td class="text-truncate">${record.customer_name}</td>
              <td class="text-truncate">
                <a href="tel:${record.customer_phone}" class="text-primary" onclick="event.stopPropagation();">${record.customer_phone}</a>
              </td>
              <td class="text-truncate">${parseFloat(record.due).toFixed(2)}</td>
              <td>${new Date(record.next_payment_date).toLocaleDateString()}</td>
            </tr>`;
          tbody.insertAdjacentHTML("beforeend", row);
        });

        document.querySelectorAll(".loan-row").forEach(row => {
          row.addEventListener("click", function () {
            window.location.href = "payments.php";
          });
        });
      }
    })
    .catch(error => console.error("Error fetching loan alerts:", error));

  // Stock Alerts
  fetch("api/dashboard_alerts.php?type=stock")
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector("#stock-alerts tbody");
      tbody.innerHTML = "";

      if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3">No products near expiry or expired.</td></tr>`;
      } else {
        data.forEach(product => {
          const expiry = new Date(product.expiry_date);
          const today = new Date();
          today.setHours(0, 0, 0, 0);

          let rowClass = "";
          if (expiry < today) {
            rowClass = 'table-danger';
          } else if ((expiry - today) / (1000 * 60 * 60 * 24) <= 2) {
            rowClass = 'table-warning';
          }

          const row = document.createElement("tr");
          row.className = rowClass;
          row.style.cursor = "pointer";

          row.innerHTML = `
            <td class="text-truncate">${product.full_name}</td>
            <td class="text-truncate">${product.quantity}</td>
            <td class="text-truncate">${expiry.toLocaleDateString()}</td>`;

          row.addEventListener("click", () => {
            window.location.href = "stock.php";
          });

          tbody.appendChild(row);
        });
      }
    })
    .catch(error => {
      console.error("Error fetching stock alerts:", error);
      const tbody = document.querySelector("#stock-alerts tbody");
      tbody.innerHTML = `<tr><td colspan="3">Failed to load stock alerts.</td></tr>`;
    });

});
