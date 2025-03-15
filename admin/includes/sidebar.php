<div id="layoutSidenav_nav">

    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Core</div>
                <a class="nav-link" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <div class="sb-sidenav-menu-heading">Tables and Menu</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseTable"
                    aria-expanded="false" aria-controls="collapseTable">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Orders
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseTable" aria-labelledby="headingOne"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="tables.php">Table</a>
                        <a class="nav-link" href="menu.php">Menu</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseUsers"
                    aria-expanded="false" aria-controls="collapseUsers">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    Management
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseUsers" aria-labelledby="headingTwo"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionAdmins">
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#adminsCollapseAuth" aria-expanded="false"
                            aria-controls="adminsCollapseAuth">
                            Edit Table & Menu
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="adminsCollapseAuth" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordionAdmins">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="login.html">Edit Table</a>
                                <a class="nav-link" href="edit-menu.php">Edit Menu</a>
                                <!-- <a class="nav-link" href="password.html">Edit Payment History</a> -->
                            </nav>
                        </div>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseError"
                    aria-expanded="false" aria-controls="pagesCollapseError">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    Customer & Bills
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="pagesCollapseError" aria-labelledby="headingOne"
                    data-bs-parent="#sidenavAccordionPages">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="customers.php">Customer ID</a>
                        <a class="nav-link" href="404.html">Payments and Bills</a>
                        <a class="nav-link" href="500.html">Sales Record</a>
                    </nav>
                </div>
                <div class="sb-sidenav-menu-heading">Manage Users</div>

                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePagesUsers"
                    aria-expanded="false" aria-controls="collapsePagesUsers">
                    <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                    Users
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapsePagesUsers" aria-labelledby="headingTwo"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav accordion" id="sidenavAccordionPagesUsers">
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#pagesCollapseAuthAdmins" aria-expanded="false"
                            aria-controls="pagesCollapseAuthAdmins">
                            Admins
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseAuthAdmins" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordionPagesUsers">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="admins.php">View Admins</a>
                                <a class="nav-link" href="add_admin.php">Edit Admins</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                            data-bs-target="#pagesCollapseAuthStaffs" aria-expanded="false"
                            aria-controls="pagesCollapseAuthStaffs">
                            Staffs
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="pagesCollapseAuthStaffs" aria-labelledby="headingOne"
                            data-bs-parent="#sidenavAccordionPagesUsers">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="view_staff.php">View Staffs</a>
                                <a class="nav-link" href="add_staff.php">Edit Staffs</a>
                            </nav>
                        </div>
                    </nav>
                </div>
            </div>
            <!-- <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseAdmins"
                    aria-expanded="false" aria-controls="collapseAdmins">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Admin
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseAdmins" aria-labelledby="headingOne"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-create.php">Add Admin</a>
                        <a class="nav-link" href="admins.php">View Admins</a>
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseStaffs"
                    aria-expanded="false" aria-controls="collapseStaffs">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    Staffs
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseStaffs" aria-labelledby="headingOne"
                    data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="admin-create.php">Add Staffs</a>
                        <a class="nav-link" href="admins.php">View Staffs</a>
                    </nav>
                </div> -->
            <!-- </div> -->
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            Admin
        </div>
    </nav>
</div>