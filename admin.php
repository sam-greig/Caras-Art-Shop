<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="website_styling.css">
    <title>Cara's Shopfront</title>
</head>

<?php
error_reporting(E_ERROR | E_WARNING | E_NOTICE);
$server_name = "";
$username = "";
$password = "";
$database_name = "";
$connect = mysqli_connect($server_name, $username, $password, $database_name);
$password_attempt = "";
$show_requests = $incorrect_pass = $is_art_page_set = $is_remove_page_set = $no_entries = False;
$hashed_password = password_hash("", PASSWORD_DEFAULT);

if (isset($_POST["hidden_art_field"])){
    $show_requests = True;
    $is_art_page_set = True;
}

if (isset($_POST['remove_btn_order_id'])) {
    $get_order_id = $_POST['remove_btn_order_id'];
    $sql = "DELETE FROM OrdersDb WHERE OrderID = $get_order_id";
    $connect->query($sql);
    $show_requests = True;
    $is_remove_page_set = True;
}

if(isset($_POST["password"])){
    $password_attempt = $_POST["password"];
    if (password_verify($password_attempt, $hashed_password)){
        $show_requests = True;
    }
    else{
        $incorrect_pass = True;
    }
}
?>
<header>
    <h1 style="color: black">Cara's Art Shop</h1>
</header>
<body>
<?php
if(!$show_requests){?>
    <div class="container">
        <form action="admin.php" method="post" style="border: 1px solid black; padding: 50px; background-color: #FFFFFF">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password">
            <input type="submit" value="Login" style="width: 70px"><?php
            if($incorrect_pass){?>
                <br><br>
                <p style="color: red; text-align: center; "><b>Incorrect Password, please try again!</b></p><?php
            }?>
        </form>
    </div><?php
}
else {?>
    <div class="container">
        <div id="view_orders" class="custom_button">
            <h2>View Orders</h2>
        </div>

        <div id="show_orders" style="display: none;">
            <?php
            $sql = "SELECT OrderID, Name, PhoneNumber, Email, PostalAddress, Art_id FROM OrdersDb";
            $result = $connect->query($sql);

            if ($result->num_rows > 0) {
                $no_entries = false;
            ?>
            <table style="width: 100%; border: 2px solid black">
                <tr style="height: 50px">
                    <th style="font-weight: bold">Order Number</th>
                    <th style="font-weight: bold">Customer</th>
                    <th style="font-weight: bold">Phone Number</th>
                    <th style="font-weight: bold">Email</th>
                    <th style="font-weight: bold">Address</th>
                    <th style="font-weight: bold">Art ID</th>
                    <th id="remove_order_title" style="font-weight: bold; display: none">Remove Order</th>
                </tr>
                <?php
                $row_number = 0;
                while($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <th><?php
                                echo $row["OrderID"];?>
                        </th>
                        <th><?php
                            echo $row["Name"];?>
                        </th>
                        <th><?php
                            echo $row["PhoneNumber"];?>
                        </th>
                        <th><?php
                            echo $row["Email"];?>
                        </th>
                        <th><?php
                            echo $row["PostalAddress"];?>
                        </th>
                        <th><?php
                            echo $row["Art_id"];?>
                        </th>
                        <th class="remove_order_buttons" style="display: none">
                            <form action="admin.php" method="post">
                                <button name="remove_btn_order_id" style="padding: 10px; color: red" value="<?php echo $row["OrderID"]?>">Remove</button>
                            </form>
                        </th>
                    </tr>
                    <?php
                    $row_number++;
                }?>
                </table><?php
        } else {
                $no_entries = true;
                echo "<p id='no_entries'>No entries stored in the database.</p>";
        }?>
        </div>

        <div id="add_painting" class="custom_button">
            <h2>Add new paintings</h2>
        </div>
        <div id="show_painting_form" style="display: none; border: 1px solid black; padding: 50px; background-color: #FFFFFF">
            <div style="display: flex; align-items: center;">
                <img src="https://img.freepik.com/free-vector/paint-brushes-color-palette_1308-127912.jpg?w=996&t=st=1698857782~exp=1698858382~hmac=c3a5ad4ebbe845c9c6380f2701bea712e8f681f818dd6142de9357c1ed9158e5" alt="Cartoon paintbrush and paint pallet" width="50px" height="50px" style="margin-right: 20%">
                <h2 style="display: inline-block">New painting form</h2>
            </div>
            <br>
            <form id="painting_form" style="text-align: left;" method="post" enctype="multipart/form-data">
                <label for="image" class="label_title">Image:</label>
                <input type="file" id="image" name="image" accept="image/*"><br><br>
                <label for="name" class="label_title">Name:</label>
                <input type="text" id="name" name="name" required><br><br>
                <label for="completion_date" class="label_title">Completion Date:</label>
                <input type="date" id="completion_date" name="completion_date" required><br><br>
                <label for="width" class="label_title">Width:</label>
                <input type="number" id="width" name="width" min="0" step=".01" required>
                <span>mm</span><br><br>
                <label for="height" class="label_title">Height:</label>
                <input type="number" id="height" name="height" min="0" step=".01" required>
                <span>mm</span><br><br>
                <label for="price" class="label_title">Price:</label>
                <input type="number" id="price" name="price" min="0" step=".01" required>
                <span>£</span><br><br>
                <label for="description" class="label_title" style="vertical-align: top">Description:</label>
                <textarea id="description" name="description" placeholder="Max 250 characters" maxlength="250" cols="25" rows="10" style="resize: none"></textarea><br><br>
                <input type="hidden" name="hidden_art_field" value="art_form_hidden">
                <button id="form_submit" style="float: right; margin-right: 7.5%">Submit</button>
            </form>
        </div>

        <?php
        $art_name = $art_description = $completion_date = $image_data = $image_type = "";
        $width = $height = $price = 0.0;

        if (isset($_POST["name"])) {
            $art_name = $_POST["name"];
        }
        if (isset($_POST["description"])) {
            $art_description = $_POST["description"];
        }
        if (isset($_POST["completion_date"])) {
            $completion_date = $_POST["completion_date"];
        }
        if (isset($_POST["width"])) {
            $width = $_POST["width"];
        }
        if (isset($_POST["height"])) {
            $height = $_POST["height"];
        }
        if (isset($_POST["price"])) {
            $price = $_POST["price"];
        }

        if (isset($_FILES["image"])) {
            $image_data = file_get_contents($_FILES["image"]["tmp_name"]);
            $image_data = base64_encode($image_data);
        }
        if ($is_art_page_set) {
            $sql = "INSERT INTO ArtDb (Name, CompletionDate, Width, Height, Price, Description, Image, Sold) VALUES ('$art_name', '$completion_date', $width, $height, $price, '$art_description', '$image_data', 0)";
            if (!($connect->query($sql) === TRUE)) {
                echo "Error: " . $sql . "<br>" . $connect->error;
            }
        }
        ?>

        <div id="remove_orders" class="custom_button">
            <h2>Remove Orders</h2>
        </div>

    </div>
    <div id="cart_button" class="custom_button" style="padding-bottom: 2px; padding-top: 2px; display: none; width: 60px; margin-left:  20px">
        <h2>Back</h2>
    </div>

<?php
}
$connect->close();
?>
<script>
    const view_orders = document.getElementById("view_orders");
    const add_painting = document.getElementById("add_painting");
    const remove_orders = document.getElementById("remove_orders");
    const back_button = document.getElementById("cart_button");
    const show_orders = document.getElementById("show_orders");
    const show_painting_form = document.getElementById("show_painting_form");
    const paint_form = document.getElementById("painting_form");
    const remove_order_title = document.getElementById("remove_order_title");
    const remove_order_buttons = document.getElementsByClassName("remove_order_buttons");
    let have_orders = <?php echo json_encode($no_entries); ?>;

    function hide_buttons() {
        view_orders.style.display = "none";
        add_painting.style.display = "none";
        remove_orders.style.display = "none";
        back_button.style.display = "block";
    }

    function show_buttons() {
        view_orders.style.display = "block";
        add_painting.style.display = "block";
        remove_orders.style.display = "block";
        back_button.style.display = "none";
    }

    view_orders.addEventListener("click", function() {
        if (show_orders.style.display === "none" || show_orders.style.display === "") {
            show_orders.style.display = "block";
            hide_buttons();
        } else {
            show_orders.style.display = "none";
        }
    });

    back_button.addEventListener("click", function() {
        if (show_orders.style.display === "block" || show_orders.style.display === "") {
            show_orders.style.display = "none";
        }
        else if (show_painting_form.style.display === "block" || show_painting_form.style.display === ""){
            show_painting_form.style.display = "none";
        }
        if(!have_orders) {
            if (remove_order_title.style.display === "table-cell") {
                remove_order_title.style.display = "none";
                for (let i = 0; i < remove_order_buttons.length; i++) {
                    remove_order_buttons[i].style.display = "none";
                }
            }
        }
        show_buttons();
    });

    add_painting.addEventListener("click", function() {
        if (show_painting_form.style.display === "none" || show_painting_form.style.display === "") {
            show_painting_form.style.display = "block";
            hide_buttons();
        } else {
            show_painting_form.style.display = "none";
        }
    });

    paint_form.addEventListener("submit", function(event) {
        event.preventDefault();
        alert("Painting added successfully!");
        paint_form.submit();
    });

    remove_orders.addEventListener("click", function() {
        if (show_orders.style.display === "none" || show_orders.style.display === "") {
            show_orders.style.display = "block";
            if (!have_orders) {
                remove_order_title.style.display = "table-cell";
                for (let i = 0; i < remove_order_buttons.length; i++) {
                    remove_order_buttons[i].style.display = "table-cell";
                }
            }
            hide_buttons();
        } else {
            show_orders.style.display = "none";
        }
    });

    const get_remove_buttons = document.querySelectorAll('.remove_order_buttons button');

    get_remove_buttons.forEach(function(get_remove_button) {
        get_remove_button.addEventListener('click', function() {
            let button_value = get_remove_button.value;
            alert('Order Number ' + button_value + ' has been removed.');
        });
    });

    let get_page_state_art_form = <?php echo $is_art_page_set ? 'true' : 'false'; ?>;
    if (get_page_state_art_form) {
        hide_buttons();
        show_painting_form.style.display = "block";
    }

    let get_page_state_remove_order = <?php echo $is_remove_page_set ? 'true' : 'false'; ?>;
    if (get_page_state_remove_order) {
        hide_buttons();
        show_orders.style.display = "block";
        remove_order_title.style.display = "table-cell";
        for (let i = 0; i < remove_order_buttons.length; i++) {
            remove_order_buttons[i].style.display = "table-cell";
        }
    }

    document.getElementById('form_submit').addEventListener('click', function(event) {
        event.preventDefault();
        let painting_form = document.getElementById('painting_form');
        let image_input = document.getElementById('image');
        let name_input = document.getElementById('name');
        let completion_date_input = document.getElementById('completion_date');
        let width_input = document.getElementById('width');
        let height_input = document.getElementById('height');
        let price_input = document.getElementById('price');

        if (!image_input.files.length > 0) {
            alert('Please select an image file.');
            return;
        }
        if (name_input.value.trim() === '') {
            alert('Please enter a name.');
            return;
        }
        if (completion_date_input.value === '') {
            alert('Please enter a completion date.');
            return;
        }
        if (isNaN(width_input.value) || width_input.value <= 0) {
            alert('Please enter a valid positive width.');
            return;
        }
        if (isNaN(height_input.value) || height_input.value <= 0) {
            alert('Please enter a valid positive height.');
            return;
        }
        if (price_input.value.trim() === '' || isNaN(price_input.value) || price_input.value < 0) {
            alert('Please enter a valid non-negative price.');
            return;
        }
        painting_form.submit();
    });


</script>
<footer>
    &copy; 2023 Cara's Art Shop
</footer>
</body>
</html>