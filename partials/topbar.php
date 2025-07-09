<div class="navbar-custom">
    <div class="topbar">
        <div class="topbar-menu d-flex align-items-center gap-lg-2 gap-1">

            <!-- Brand Logo -->
            <div class="logo-box">
                <!-- Light Logo -->
                <a href="dashboard.php" class="logo-light">
                    <img src="assets/images/logo-light.png" alt="logo" class="logo-lg" height="50">
                    <img src="assets/images/logo-sm.png" alt="small logo" class="logo-sm" height="25">
                </a>
                <!-- Dark Logo -->
                <a href="dashboard.php" class="logo-dark">
                    <img src="assets/images/logo-dark.png" alt="dark logo" class="logo-lg" height="50">
                    <img src="assets/images/logo-sm.png" alt="small logo" class="logo-sm" height="25">
                </a>
            </div>

            <!-- Sidebar Toggle Button -->
            <button class="button-toggle-menu">
                <i class="mdi mdi-menu"></i>
            </button>
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-4">
            <!-- Create Invoice Button and Modal -->
            <li>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#invoiceModal">
                    Create Invoice
                </button>

                <div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="invoiceModalLabel">Create Invoice</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="invoiceForm" method="post">
                                    <div class="container-fluid" style="max-width: 1000px;">
                                        <div class="row mt-3">
                                            <!-- Customer -->
                                            <div class="col-12 mt-2">
                                                <label class="form-label">Customer:</label>
                                                <select name="customer" id="customerSelect" class="form-control w-50 d-inline" required>
                                                    <option value="">Loading customers...</option>
                                                </select>
                                            </div>
                                            <!-- Invoice No -->
                                            <div class="col-12 mt-3">
                                                <label class="form-label">Invoice No:</label>
                                                <input type="text" id="invno" name="invno" class="form-control w-50 d-inline" readonly />
                                            </div>
                                            <!-- Invoice Date -->
                                            <div class="col-12 mt-3">
                                                <label class="form-label">Invoice Date:</label>
                                                <input type="date" id="invdate" name="invdate" class="form-control w-50 d-inline" readonly />
                                            </div>

                                            <!-- Invoice Table -->
                                            <div class="table-responsive mt-4">
                                                <table id="invoiceTable" class="table table-striped w-100">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Name</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                            <th>Amount</th>
                                                            <th>
                                                                <button type="button" class="btn btn-info btn-sm" onclick="addRow()">+</button>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tableBody">
                                                        <tr>
                                                            <td>1</td>
                                                            <td>
                                                                <select name="particular[]" class="form-control particularSelect" required>
                                                                    <option value="">Loading...</option>
                                                                </select>
                                                            </td>
                                                            <td><input type="number" name="qty[]" class="qty form-control" oninput="calculateAmount(this)" /></td>
                                                            <td><input type="number" name="rate[]" class="rate form-control" oninput="calculateAmount(this)" /></td>
                                                            <td><input type="text" name="amount[]" class="amount form-control" readonly /></td>
                                                            <td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Totals -->
                                            <div class="col-12 mt-3">
                                                <label class="form-label">Total:</label>
                                                <input type="text" id="total" name="total" class="form-control w-25 d-inline" readonly />
                                            </div>
                                            <div class="col-12 mt-3">
                                                <label class="form-label">Paid:</label>
                                                <input type="number" id="paid" name="paid" class="form-control w-25 d-inline" oninput="calculateTotal()" />
                                            </div>
                                            <div class="col-12 mt-3">
                                                <label class="form-label">Due Amount:</label>
                                                <input type="text" id="dueamt" name="dueamt" class="form-control w-25 d-inline" readonly />
                                            </div>

                                            <!-- Payment Method -->
                                            <div class="col-12 mt-3">
                                                <label class="form-label me-2">Payment Method:</label>
                                                <input type="radio" id="online" name="payment_method" value="Online" required />
                                                <label for="online" class="me-3">Online</label>
                                                <input type="radio" id="cash" name="payment_method" value="Cash" required />
                                                <label for="cash">Cash</label>
                                            </div>
                                            <div class="col-12 mt-3">
                                                <label class="form-label">Next Payment Date:</label>
                                                <input type="date" name="next_payment_date" class="form-control w-25 d-inline" />
                                            </div>
                                            <div class="col-12 mt-4">
                                                <button type="submit" class="btn btn-success">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Receipt Modal -->
            <li>

                <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="receiptModalLabel">Receipt</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="receipt-section">
                                    <div class="tm_pos_invoice_wrap" id="tm_download_section">
                                        <div class="tm_pos_invoice_top">
                                            <div class="tm_pos_company_logo">
                                                <img src="assets/images/logo.png" alt="Company Logo">
                                            </div>
                                            <div class="tm_pos_company_name">Afzaal Butt & CO.</div>
                                            <div class="tm_pos_company_address">86-90 Paul Street, London, England</div>
                                            <div class="tm_pos_company_mobile">Phone: 0308 9073037</div>
                                        </div>

                                        <div class="tm_pos_invoice_body">
                                            <div class="tm_pos_invoice_heading">
                                                <span>Retail Receipt</span>
                                            </div>
                                            <ul class="tm_list tm_style1">
                                                <li><div class="tm_list_title">Customer:</div><div class="tm_list_desc" id="receipt-customer">-</div></li>
                                                <li class="text-right"><div class="tm_list_title">Invoice No:</div><div class="tm_list_desc" id="receipt-invno">-</div></li>
                                                <li><div class="tm_list_title">Date:</div><div class="tm_list_desc" id="receipt-date">-</div></li>
                                            </ul>

                                            <table class="tm_pos_invoice_table">
                                                <thead>
                                                    <tr><th>SL</th><th>Item</th><th>Rate</th><th>Qty</th><th>Total</th></tr>
                                                </thead>
                                                <tbody id="receipt-items"></tbody>
                                            </table>

                                            <div class="tm_bill_list">
                                                <div class="tm_bill_list_in"><div class="tm_bill_title">Sub-Total:</div><div class="tm_bill_value" id="receipt-total">0</div></div>
                                                <div class="tm_bill_list_in"><div class="tm_bill_title">Paid:</div><div class="tm_bill_value" id="receipt-paid">0</div></div>
                                                <div class="tm_bill_list_in"><div class="tm_bill_title">Due Amount:</div><div class="tm_bill_value" id="receipt-dueamt">0</div></div>
                                                <div class="tm_bill_list_in"><div class="tm_bill_title">Payment Method:</div><div class="tm_bill_value" id="receipt-payment-method">-</div></div>
                                                <div class="tm_bill_list_in"><div class="tm_bill_title">Next Payment Date:</div><div class="tm_bill_value" id="receipt-next-payment-date">-</div></div>
                                            </div>
                                            <div class="tm_pos_invoice_footer">Powered by Mandi Manager</div>
                                        </div>
                                    </div>

                                    <div class="tm_invoice_btns tm_hide_print mt-3">
                                        <button id="tm_download_btn" class="tm_invoice_btn tm_color2">
                                            <span class="tm_btn_icon"><i class="fas fa-download"></i></span>
                                            <span class="tm_btn_text">Download</span>
                                        </button>
                                        <button id="tm_print_btn" class="tm_invoice_btn tm_color1">
                                            <span class="tm_btn_icon"><i class="fas fa-print"></i></span>
                                            <span class="tm_btn_text">Print</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Notifications -->
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle waves-effect waves-light arrow-none" data-bs-toggle="dropdown" href="#" role="button">
                    <i class="mdi mdi-bell font-size-24"></i>
                    <span class="badge bg-danger rounded-circle noti-icon-badge" style="display: none;">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated dropdown-lg py-0" style="width: 360px;">
                    <div class="p-2 border-bottom border-dashed">
                        <div class="row align-items-center">
                            <div class="col"><h6 class="m-0 font-size-16 fw-semibold">Notification</h6></div>
                            <div class="col-auto"><a href="javascript:void(0);" class="text-dark text-decoration-underline" id="clear-all-btn"><small>Clear All</small></a></div>
                        </div>
                    </div>
                    <div id="notification-content" data-simplebar style="max-height: 300px; overflow-y: auto;" class="px-1">
                        <!-- JS inserts notifications here -->
                    </div>
                </div>
            </li>

            <!-- Theme Switch -->
            <li class="nav-link" id="theme-mode">
                <i class="bx bx-moon font-size-24"></i>
            </li>

            <!-- User Profile -->
            <li class="dropdown">
                <a class="nav-link dropdown-toggle nav-user me-0 waves-effect waves-light" data-bs-toggle="dropdown" href="#" role="button">
                    <img src="assets/images/users/avatar.png" alt="user-image" class="rounded-circle">
                    <span class="username" style="margin-left: 10px;">
                        <?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Guest'; ?>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div>
                    <a href="api/logout.php" class="dropdown-item notify-item">
                        <i data-lucide="log-out" class="font-size-16 me-2"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/jspdf.min.js"></script>
<script src="assets/js/html2canvas.min.js"></script>
<script src="assets/js/invoice.js"></script>
<script src="assets/libs/simplebar/simplebar.min.js"></script>
<script src="assets/js/notification.js"></script>
