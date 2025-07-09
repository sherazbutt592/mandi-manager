let dataTable;
// Fetch and display customers after the page loads
document.addEventListener("DOMContentLoaded", function() {
    fetchCustomers();
});
function initializeDataTable() {
  console.log("Initializing DataTable...");
  dataTable = $('#datatable-buttons').DataTable();
  fetchCustomers();
}
function getCreditLabel(score) {
    if (score >= 90) return '<span class="badge bg-success">Excellent</span>';
    if (score >= 70) return '<span class="badge bg-primary">Good</span>';
    if (score >= 50) return '<span class="badge bg-warning">Average</span>';
    if (score >= 30) return '<span class="badge bg-danger">Poor</span>';
    return '<span class="badge bg-danger">Risky</span>';
}

// Function to fetch all customers and display them in the table
function fetchCustomers() {
    fetch('api/customer_handler.php?action=fetch')
    .then(response => response.json())
    .then(data => {
        const table = $('#datatable-buttons');

        // ✅ Destroy existing instance if already initialized
        if ($.fn.DataTable.isDataTable('#datatable-buttons')) {
            table.DataTable().destroy();
        }

        const tableBody = document.querySelector("#datatable-buttons tbody");
        tableBody.innerHTML = ''; // Clear previous rows

        if (data && data.data) {
            data.data.forEach(customer => {
                const row = document.createElement("tr");
                row.setAttribute('data-customer-id', customer.id);
                row.innerHTML = `
                    <td>${customer.name}</td>
                    <td>${customer.phone}</td>
                    <td>${customer.address}</td>
                    <td>${customer.credit_score} ${getCreditLabel(customer.credit_score)}</td>
                    <td class="actions-icons">
                        <i class="fas fa-times" title="Remove Row" onclick="removeCustomer(this)"></i>
                        <i class="fas fa-edit" title="Edit Customer" data-bs-toggle="modal" data-bs-target="#editCustomerModal"></i>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            // ✅ Re-initialize after data is inserted
            dataTable = table.DataTable({
                dom: 'Bfrtip',
                buttons: ['copy', 'pdf'],
                responsive: true,
                scrollX: true
            });

        } else {
            console.error('Invalid data format:', data);
            alert("Failed to load customer data.");
        }
    })
    .catch(error => {
        console.error("Error fetching customers:", error);
        alert("An error occurred while fetching customers.");
    });
}


// Handle Save Customer Button Click
document.getElementById("saveCustomerBtn").addEventListener("click", function () {
    const name = document.getElementById("customerName").value;
    const phone = document.getElementById("customerPhone").value;
    const address = document.getElementById("customerAddress").value;
    const creditScore = 100; // Default credit score (could be changed later)

    // Validate input
    if (!name || !phone || !address) {
        alert("Please fill in all fields.");
        return;
    }

    // Prepare data to send via AJAX
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('address', address);
    formData.append('credit_score', creditScore);

    // Send AJAX request to add customer
    fetch('api/customer_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data); // For debugging, log the response from the server
        if (data === 'success') {
            alert("Customer added successfully!");
            // After success, fetch customers again to update the table
            fetchCustomers();
            
            // Reset the form after success
            document.getElementById("addCustomerForm").reset();

            // Close the modal after adding the customer
            const modal = bootstrap.Modal.getInstance(document.getElementById('addCustomerModal'));
            modal.hide();
        } else {
            alert("Error: " + data); // Display error if insertion fails
        }
    })
    .catch(error => {
        console.error("Error occurred while adding customer:", error);
        alert("An error occurred while adding the customer.");
    });
});

// Handle Remove Customer
function removeCustomer(iconElement) {
    const row = iconElement.closest('tr');
    const customerId = row.getAttribute('data-customer-id');  // Get the customer ID from the row

    // Confirm before removing the customer
    if (confirm("Are you sure you want to delete this customer?")) {
        // Send AJAX request to delete the customer
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', customerId);

        fetch('api/customer_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // For debugging
            if (data === 'success') {
                alert("Customer deleted successfully!");
                row.remove();  // Remove the row from the table
            } else {
                alert("Error: " + data); // Display error if deletion fails
            }
        })
        .catch(error => {
            console.error("Error occurred while deleting customer:", error);
            alert("An error occurred while deleting the customer.");
        });
    }
}


// Handle Save Edit Customer Button Click
document.getElementById("saveEditCustomerBtn").addEventListener("click", function () {
    const id = document.getElementById("editCustomerId").value;
    const name = document.getElementById("editCustomerName").value;
    const phone = document.getElementById("editCustomerPhone").value;
    const address = document.getElementById("editCustomerAddress").value;

    if (!name || !phone || !address) {
        alert("Please fill in all fields.");
        return;
    }

    // Prepare data to send via AJAX
    const formData = new FormData();
    formData.append('action', 'edit');
    formData.append('id', id);
    formData.append('name', name);
    formData.append('phone', phone);
    formData.append('address', address);

    // Send AJAX request to update customer
    fetch('api/customer_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
            alert("Customer updated successfully!");

            // Dynamically update the row in the table with new customer data
            const row = document.querySelector(`tr[data-customer-id='${id}']`);
            row.querySelector("td:nth-child(1)").textContent = name;
            row.querySelector("td:nth-child(2)").textContent = phone;
            row.querySelector("td:nth-child(3)").textContent = address;

            // Close the modal after updating the customer
            const modal = bootstrap.Modal.getInstance(document.getElementById('editCustomerModal'));
            modal.hide();  // This closes the modal immediately after saving
        }
    )
    .catch(error => {
        console.error("Error occurred while editing customer:", error);
        alert("An error occurred while editing the customer.");
    });
});

// Edit Customer
document.querySelector("#datatable-buttons").addEventListener("click", function (e) {
    if (e.target && e.target.matches(".fa-edit")) {
        const row = e.target.closest("tr");
        const customerId = row.getAttribute("data-customer-id");

        // Fetch customer data to edit
        fetch(`api/customer_handler.php?action=fetchById&id=${customerId}`)
        .then(response => response.json())
        .then(customer => {
            // Populate the form fields in the Edit modal
            document.getElementById("editCustomerId").value = customer.id;
            document.getElementById("editCustomerName").value = customer.name;
            document.getElementById("editCustomerPhone").value = customer.phone;
            document.getElementById("editCustomerAddress").value = customer.address;
        })
        .catch(error => {
            console.error("Error fetching customer details:", error);
            alert("An error occurred while fetching customer details.");
        });
    }
});


