<?php
session_start();
include('config/config.php');
include('config/checklogin.php');
check_login();
include("includes/header.php");
include("includes/analytics.php");
?>

<style>
    .card-custom {
        background-color: white;
        border: 1px solid black;
        color: black;
    }

    .card-custom .card-footer a {
        color: black;
    }
</style>

<div class="container-fluid px-4">
    <h4 class="mt-2 mb-3">Dashboard</h4>
    <!-- <div style="background-image: url(assets/img/theme/restro00.jpg); background-size: cover;"
        class="header  pb-8 pt-5 pt-md-8"> -->
    <span class="mask bg-gradient-dark opacity-8"></span>
    <div class="container-fluid">
        <div class="header-body">
            <!-- Card stats -->
            <div class="row">
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">total Orders</h5>
                                    <!-- <span class="h2 font-weight-bold mb-0"><?php echo $orders; ?></span> -->
                                </div>

                                <!-- For more projects: Visit codeastro.com  -->
                                <!-- <div class="col-auto">
                                    <div class="icon icon-shape bg-danger text-white rounded-circle shadow">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Available Tables</h5>
                                    <!-- <span class="h2 font-weight-bold mb-0"><?php echo $products; ?></span> -->
                                </div>
                                <!-- <div class="col-auto">
                                    <div class="icon icon-shape bg-primary text-white rounded-circle shadow">
                                        <i class="fas fa-utensils"></i>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Bills Pending</h5>
                                    <!-- <span class="h2 font-weight-bold mb-0"><?php echo $customers; ?></span> -->
                                </div>
                                <!-- <div class="col-auto">
                                    <div class="icon icon-shape bg-warning text-white rounded-circle shadow">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-6">
                    <div class="card card-stats mb-4 mb-xl-0">
                        <div class="card-body">
                            <div class="row">
                                <div class="col">
                                    <h5 class="card-title text-uppercase text-muted mb-0">Total Sales</h5>
                                    <!-- <span class="h2 font-weight-bold mb-0">Rs.<?php echo $sales; ?></span> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- </div> -->
    <!-- <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol> -->
    <!-- <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom mb-4">
                <div class="card-body"><strong>Total Orders</strong></div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="#">View Details</a>
                    <div class="small"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom mb-4">
                <div class="card-body"><strong>Tables Available</strong></div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="#">View Details</a>
                    <div class="small"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom mb-4">
                <div class="card-body"><strong>Orders Pending</strong></div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="#">View Details</a>
                    <div class="small"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card card-custom mb-4">
                <div class="card-body"><strong>Total Sales</strong></div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" href="#">View Details</a>
                    <div class="small"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div> -->

    <?php include("includes/footer.php"); ?>