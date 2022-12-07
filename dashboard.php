<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }

        table tr td:last-child {
            width: 120px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</head>

<body>
    <?php
    // Include config file
    require_once "connect_db.php";

    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token['access_token']);

        // get profile info
        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $userinfo = [
            'email' => $google_account_info['email'],
            'first_name' => $google_account_info['givenName'],
            'last_name' => $google_account_info['familyName'],
            'gender' => $google_account_info['gender'],
            'full_name' => $google_account_info['name'],
            'picture' => $google_account_info['picture'],
            'verifiedEmail' => $google_account_info['verifiedEmail'],
            'token' => $google_account_info['id'],
        ];

        // checking if user is already exists in database
        $sql = "SELECT * FROM users WHERE email ='{$userinfo['email']}'";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            // user is exists
            $userinfo = mysqli_fetch_assoc($result);
            $token = $userinfo['token'];
        } else {
            // user is not exists
            $sql = "INSERT INTO users (email, first_name, last_name, gender, full_name, picture, verifiedEmail, token) VALUES ('{$userinfo['email']}', '{$userinfo['first_name']}', '{$userinfo['last_name']}', '{$userinfo['gender']}', '{$userinfo['full_name']}', '{$userinfo['picture']}', '{$userinfo['verifiedEmail']}', '{$userinfo['token']}')";
            $result = mysqli_query($link, $sql);
            if ($result) {
                $token = $userinfo['token'];
            } else {
                echo "User is not created";
                die();
            }
        }

        // save user data into session
        $_SESSION['user_token'] = $token;
    } else {
        if (!isset($_SESSION['user_token'])) {
            header("Location: index.php");
            die();
        }

        // checking if user is already exists in database
        $sql = "SELECT * FROM users WHERE token ='{$_SESSION['user_token']}'";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            // user is exists
            $userinfo = mysqli_fetch_assoc($result);
        }
    }
    $result = mysqli_query($link, 'select count(id) as total from bota_product');
    $row = mysqli_fetch_assoc($result);
    $total_records = $row['total'];
    $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
    $limit = 20;
    $total_page = ceil($total_records / $limit);
    if ($current_page > $total_page) {
        $current_page = $total_page;
    } else if ($current_page < 1) {
        $current_page = 1;
    }
    $start = ($current_page - 1) * $limit;
    $sql = "SELECT * FROM bota_product LIMIT $start, $limit";
    ?>

    <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

            <!-- Sidebar Toggle (Topbar) -->
            <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                <i class="fa fa-bars"></i>
            </button>

            <!-- Topbar Search -->
            <form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                <div class="input-group">
                    <input type="text" class="form-control bg-light border-0 small" placeholder="Search for..." aria-label="Search" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="button">
                            <i class="fas fa-search fa-sm"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Topbar Navbar -->
            <ul class="navbar-nav ml-auto">
                <div class="topbar-divider d-none d-sm-block"></div>
                <!-- Nav Item - User Information -->
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= $userinfo['full_name'] ?></span>
                        <img class="img-profile rounded-circle" src="<?= $userinfo['picture'] ?>" width="30px" height="30px">
                    </a>
                    <!-- Dropdown - User Information -->
                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                            Email Address: <?= $userinfo['email'] ?>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item">
                            <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                            Logout
                        </a>
                    </div>
                </li>

            </ul>

        </nav>
        <!-- End of Topbar -->


    </div>
    <div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">

                    <div class="mt-5 mb-3 clearfix">
                        <h2 class="pull-left">Employees Details</h2>
                        <a href="create.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add New Employee</a>
                    </div>

                    <?php
                    if ($result = mysqli_query($link, $sql)) {
                        if (mysqli_num_rows($result) > 0) {
                            echo '<table class="table table-bordered table-striped">';
                            echo "<thead>";
                            echo "<tr>";
                            echo "<th>#</th>";
                            echo "<th>Title</th>";
                            echo "<th>Description</th>";
                            echo "<th>Image</th>";
                            echo "<th>Price</th>";
                            echo "<th>Action</th>";
                            echo "</tr>";
                            echo "</thead>";
                            echo "<tbody>";
                            while ($row = mysqli_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['title'] . "</td>";
                                echo "<td>" . $row['description'] . "</td>";
                                echo "<td>";
                                echo '<img  src="Uploads/' . $row['img'] . '" width="200px" </img>';
                                echo "</td>";
                                echo "<td>" . $row['price'] . "</td>";
                                echo "<td>";
                                echo '<a href="read.php?id=' . $row['id'] . '" class="mr-3" title="View Record" data-toggle="tooltip"><span class="fa fa-eye"></span></a>';
                                echo '<a href="update.php?id=' . $row['id'] . '" class="mr-3" title="Update Record" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                                echo '<a href="delete.php?id=' . $row['id'] . '" title="Delete Record" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
                                echo "</td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                            // Free result set
                            mysqli_free_result($result);
                        } else {
                            echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                        }
                    } else {
                        echo "Oops! Something went wrong. Please try again later.";
                    }

                    // Close connection
                    mysqli_close($link);
                    ?>
                </div>
                <nav aria-label="Page navigation example mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php if ($current_page <= 1) {
                                                    echo 'disabled';
                                                } ?>">
                            <a class="page-link" href="<?php if ($total_page <= 1) {
                                                            echo '#';
                                                        } else {
                                                            echo "?page=" . $prev;
                                                        } ?>">Previous</a>
                        </li>
                        <?php for ($i = 1; $i <= $total_page; $i++) : ?>
                            <li class="page-item <?php if ($current_page == $i) {
                                                        echo 'active';
                                                    } ?>">
                                <a class="page-link" href="dashboard.php?page=<?= $i; ?>"> <?= $i; ?> </a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php if ($total_page >= $total_page) {
                                                    echo 'disabled';
                                                } ?>">
                            <a class="page-link" href="<?php if ($total_page >= $total_page) {
                                                            echo '#';
                                                        } else {
                                                            echo "?page=" . $next;
                                                        } ?>">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</body>

</html>