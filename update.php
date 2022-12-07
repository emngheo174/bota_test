<?php
require_once "connect_db.php";

$title = $address = $salary = $img = $user_id = "";
$name_err = $address_err = $salary_err = "";

if (isset($_POST["id"]) && !empty($_POST["id"])) {
    $id = $_POST["id"];

    $input_name = trim($_POST["title"]);
    if (empty($input_name)) {
        $name_err = "Please enter a name.";
    } else {
        $title = $input_name;
    }

    $input_address = trim($_POST["address"]);
    if (empty($input_address)) {
        $address_err = "Please enter an address.";
    } else {
        $address = $input_address;
    }

    $input_salary = trim($_POST["salary"]);
    if (empty($input_salary)) {
        $salary_err = "Please enter the salary amount.";
    } elseif (!ctype_digit($input_salary)) {
        $salary_err = "Please enter a positive integer value.";
    } else {
        $salary = $input_salary;
    }
    $target = "Uploads/" . basename($_FILES['image']['name']);

    $user_id = trim($_POST["user_id"]);
    if (empty($name_err) && empty($address_err) && empty($salary_err)) {
        if ($_FILES["image"]["tmp_name"] != "") {
            $product_picture = $_FILES["image"]["name"];
            $target_dir = "Uploads/";
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            $sql = "Update bota_product Set title='$title', description='$address', price='$salary', img='$product_picture' Where id='$id'";
        } else {
            $sql = "UPDATE bota_product SET title='$title', description='$address', price='$salary', user_id='$user_id' WHERE id='$id'";
        }

        mysqli_query($link, $sql);
            if(mysqli_query($link, $sql) !== ""){
                header("location: index.php");
                exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
  
        mysqli_close($link);
    }

} else {
    if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
        $id =  trim($_GET["id"]);

        $sql = "SELECT * FROM bota_product WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $param_id);

            $param_id = $id;

            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) == 1) {
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    $title = $row["title"];
                    $address = $row["description"];
                    $salary = $row["price"];
                    $img = $row["img"];
                    $user_id = $row["user_id"];
                } else {
                    header("location: error.php");
                    exit();
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        mysqli_stmt_close($stmt);

        // Close connection
        mysqli_close($link);
    } else {
        header("location: error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper {
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5">Update Record</h2>
                    <p>Please edit the input values and submit to update the employee record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="title" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                            <span class="invalid-feedback"><?php echo $name_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control <?php echo (!empty($address_err)) ? 'is-invalid' : ''; ?>"><?php echo $address; ?></textarea>
                            <span class="invalid-feedback"><?php echo $address_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="text" name="salary" class="form-control <?php echo (!empty($salary_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $salary; ?>">
                            <span class="invalid-feedback"><?php echo $salary_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Image</label>\<?php echo $img ?>
                            <img src="Uploads/<?php echo $img ?>" alt="image" width="200">
                            <input type="file" name="image" class="form-control">
                            <span class="invalid-feedback"><?php echo $salary_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Salary</label>
                            <input type="text" name="user_id" class="form-control" value="<?php echo $user_id; ?>">
                            <span class="invalid-feedback"><?php echo $salary_err; ?></span>
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>" />
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>