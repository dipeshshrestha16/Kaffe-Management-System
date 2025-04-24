<?php include("includes/header.php"); ?>



<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">


<style>
    .full-height {
        height: 80vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 84px;
    }

    .links>a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
</style>


<div class="flex-center position-ref full-height">
    <div class="content">
        <div class="title m-b-md">
            Kaffe Point Of Sale
        </div>

        <div class="links">
            <a href="adminlogin.php">Admin Log In</a>
            <a href="stafflogin.php">Cashier Log In</a>
        </div>
    </div>
</div>

<?php include("includes/footer.php"); ?>