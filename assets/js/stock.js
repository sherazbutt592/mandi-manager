let dataTable;

// Initialize DataTable and fetch data
window.onload = () => {
  console.log("Page loaded");
  initializeDataTable();
  bindAddProductHandler();
};

function initializeDataTable() {
  console.log("Initializing DataTable...");
  dataTable = $('#datatable-buttons').DataTable();
  fetchProducts();
}

// Fetch products and populate DataTable
function fetchProducts() {
  console.log("Fetching products...");
  fetch('api/stock_handler.php', {
    method: 'POST',
    body: new URLSearchParams({ action: 'fetch' })
  })
    .then(res => res.json())
    .then(({ data }) => {
      console.log("Data received:", data);

      if (!Array.isArray(data)) {
        console.error("Invalid data format");
        return;
      }

      dataTable.clear();

      data.forEach(item => {
        dataTable.row.add([
          item.id,
          item.name,
          item.serial_no,
          item.quantity,
          item.date_added,
          item.expiry_date,
          `
            <div class="actions-icons">
              <i class="fas fa-times" onclick="removeProduct(this)" title="Delete"></i>
              <i class="fas fa-edit" onclick="editProduct(this)" data-bs-toggle="modal" data-bs-target="#editProductModal" title="Edit"></i>
            </div>
          `
        ]);
      });

      dataTable.draw();
    })
    .catch(err => console.error("Fetch error:", err));
}

// Automatically set today's date in Add Product modal
$('#addProductModal').on('shown.bs.modal', () => {
  const dateInput = document.getElementById("dateAdded");
  if (dateInput) {
    dateInput.value = new Date().toISOString().split('T')[0];
  }
});

// Bind add product button
function bindAddProductHandler() {
  document.getElementById("saveAddProductBtn").addEventListener("click", () => {
    const formData = new FormData();
    formData.append("action", "add");
    formData.append("name", document.getElementById("productName").value);
    formData.append("serial_no", document.getElementById("serialNo").value);
    formData.append("quantity", document.getElementById("quantity").value);
    formData.append("date_added", document.getElementById("dateAdded").value);
    formData.append("expiry_date", document.getElementById("expiryDate").value);

    fetch("api/stock_handler.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(res => {
        if (res.status === "success") {
          alert("Product added successfully.");
          fetchProducts();
          document.getElementById("addProductForm").reset();
          document.querySelector("#addProductModal .btn-close").click();
        } else {
          alert("Failed to add product: " + (res.status || JSON.stringify(res)));
        }
      })
      .catch(err => console.error("Add product error:", err));
  });
}

// Delete product by ID
function removeProduct(element) {
  if (!confirm("Are you sure you want to delete this product?")) return;

  const row = element.closest("tr");
  const id = row.cells[0].innerText;

  fetch("api/stock_handler.php", {
    method: "POST",
    body: new URLSearchParams({ action: "delete", id: id })
  })
    .then(res => res.json())
    .then(res => {
      if (res.status === "success") {
        alert("Product deleted successfully.");
        dataTable.row(row).remove().draw();
      } else {
        alert("Failed to delete product: " + res.status || JSON.stringify(res));
      }
    })
    .catch(err => console.error("Delete error:", err));
}

// Fill edit modal with row data
function editProduct(element) {
  const row = element.closest('tr');

  document.getElementById("editProductId").value = row.cells[0].innerText.trim();
  document.getElementById("editProductName").value = row.cells[1].innerText.trim();
  document.getElementById("editSerialNo").value = row.cells[2].innerText.trim();
  document.getElementById("editSerialNoOriginal").value = row.cells[2].innerText.trim();
  document.getElementById("editQuantity").value = row.cells[3].innerText.trim();
  document.getElementById("editExpiryDate").value = row.cells[5].innerText.trim();
}

// Update product handler
document.getElementById("saveEditProductBtn").addEventListener("click", () => {
  const name = document.getElementById("editProductName").value.trim();
  const serial_no = document.getElementById("editSerialNo").value.trim();
  const serial_no_original = document.getElementById("editSerialNoOriginal").value.trim();
  const quantity = document.getElementById("editQuantity").value.trim();
  const expiry_date = document.getElementById("editExpiryDate").value.trim();

  if (!name || !serial_no || !quantity || !expiry_date) {
    alert("Please fill all required fields.");
    return;
  }

  const formData = new FormData();
  formData.append("action", "update");
  formData.append("name", name);
  formData.append("serial_no", serial_no);
  formData.append("serial_no_original", serial_no_original);
  formData.append("quantity", quantity);
  formData.append("expiry_date", expiry_date);

  fetch("api/stock_handler.php", {
    method: "POST",
    body: formData
  })
    .then(res => res.text())
    .then(res => {
      if (res.trim() === "success") {
        alert("Product updated successfully.");
        fetchProducts();
        const modal = bootstrap.Modal.getInstance(document.getElementById("editProductModal"));
        modal.hide();
      } else {
        alert("Failed to update product: " + res);
      }
    })
    .catch(err => {
      console.error("Update product error:", err);
      alert("An error occurred while updating the product.");
    });
});
