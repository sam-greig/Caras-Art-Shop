<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="website_styling.css">
    <title>Cara's Shopfront</title>
    <style>
        .content_container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .art_item {
            display: flex;
            align-items: center;
            margin: 20px;
            max-width: 600px;
        }
        .picture {
            flex: 1;
            margin-right: 20px;
        }
        .picture img {
            max-width: 100%;
            height: auto;
        }
        .picture_details {
            flex: 1;
            text-align: left;
        }
        .order_form {
            width: auto;
            margin-bottom: 50px;
        }

        @media (max-width: 900px) {
            .art_item {
                flex-direction: column;
                align-items: center;
                max-width: 40%;
            }
        }
    </style>
</head>


<?php
error_reporting(E_ERROR | E_WARNING | E_NOTICE);
$server_name = "";
$username = "";
$password = "";
$database_name = "";
$connect = mysqli_connect($server_name, $username, $password, $database_name);
$art_id = 0;
$art_selected = $order_processed = false;
$image_art_id = $order_number = 0;
$client_name = $client_number = $client_email = $client_address = "";
$row = array();

$data_array =[];
if (isset($_GET['data'])) {
    $data_array_encoded = $_GET['data'];
    $data_array_decoded = base64_decode($data_array_encoded, true);
    if ($data_array_decoded) {
        $data_array = json_decode(urldecode($data_array_decoded), true);
        if ($data_array !== null) {
            if (sizeof($data_array) > 0) {
                $art_selected = true;
            }
        }
    }
}

if(isset($_POST['order_submitted'])){
    $order_processed = true;
    $data_array_JSON = $_POST['order_submitted'];
    $data_array = json_decode($data_array_JSON, true);
    $client_name = mysqli_real_escape_string($connect, $_POST['name']);
    $client_number = mysqli_real_escape_string($connect, $_POST['phone_number']);
    $client_email = mysqli_real_escape_string($connect, $_POST['email']);
    $client_address = mysqli_real_escape_string($connect,$_POST['line_1']) . ', ';
    if(!($_POST['line_2'] == "")){
        $client_address = $client_address . mysqli_real_escape_string($connect,$_POST['line_2']) . ', ';
    }
    $client_address = $client_address . mysqli_real_escape_string($connect,$_POST['town_or_city']) . ', ' . mysqli_real_escape_string($connect,$_POST['county']) . ', ' . mysqli_real_escape_string($connect,$_POST['postcode']);

    $insert_statement = mysqli_prepare($connect, "INSERT INTO OrdersDb (OrderID, Name, PhoneNumber, Email, PostalAddress, Art_id) VALUES (?, ?, ?, ?, ?, ?)");
    $update_statement = mysqli_prepare($connect, "UPDATE ArtDb SET Sold = 1 WHERE Art_id = ?");

    mysqli_stmt_bind_param($insert_statement, "issssi", $max_order_id, $client_name, $client_number, $client_email, $client_address, $image_art_id);

    foreach ($data_array as $image_art_id) {
        mysqli_stmt_execute($insert_statement);
        mysqli_stmt_bind_param($update_statement, "i", $image_art_id);
        mysqli_stmt_execute($update_statement);
    }
}
?>


<header>
    <h1 style="color: black">Cara's Art Shop</h1>
</header>
<body>
<br><br>
<div class="content_container" <?php if(!$art_selected){?>style="display: none" <?php }?>>
    <?php
    if ($art_selected) {
        foreach ($data_array as $image_art_id) {
            $sql = "SELECT * FROM ArtDb WHERE Art_ID = $image_art_id";
            $result = mysqli_query($connect, $sql);
            $row = $result->fetch_assoc();
            $image_name = $row['Name'];
            $image_completion_date = $row['CompletionDate'];
            $image_width = $row['Width'];
            $image_height = $row['Height'];
            $image_price = $row['Price'];
            $image_description = $row['Description'];
            $image_data = $row['Image'];
            $image_type = 'image/jpeg';
            ?>
            <div class="art_item">
                <div class="picture">
                    <img class='framed_image' src='data:<?php echo $image_type; ?>;base64,<?php echo $image_data; ?>' alt='<?php echo $image_description; ?>'>
                </div>
                <div class="picture_details">
                    <p><b>Name:</b> <?php echo $image_name; ?></p>
                    <p><b>Completion Date:</b> <?php echo $image_completion_date; ?></p>
                    <p><b>Width by Height:</b> <?php echo $image_width; ?>mm x <?php echo $image_height; ?>mm</p>
                    <p><b>Price:</b> £<?php echo $image_price; ?></p>
                    <p><b>Description:</b> <?php echo $image_description; ?></p>
                </div>
            </div>
            <?php
        }
    }
    ?>
</div>
<div class="content_container" <?php if(!$art_selected){?>style="display: none" <?php }?>>
    <div class="order_form" style="align-content: center; border: 1px solid black; padding: 30px; background-color: #FFFFFF;">
        <h2>Order Form</h2>
        <form action="order.php" method="post" onsubmit="return validate_form()">
                <label for="name" class="label_title" style="display: inline; padding-right: 40px">Name:</label>
                <input type="text" id="name" name="name" style="display: inline" required><br><br>
                <label for="phone_number" class="label_title" style="display: inline; padding-right: 30px">Phone:</label>
                <input type="tel" id="phone_number" name="phone_number" style="display: inline" required><br><br>
                <label for="email" class="label_title" style="display: inline; padding-right: 30px">Email:</label>
                <input type="email" id="email" name="email" style="display: inline" required><br><br>
                <label class="label_title">Address:</label><br><br>
                <label for="line_1" style="display: inline; padding-right: 30px">Line 1</label>
                <input type="text" id="line_1" name="line_1" style="display: inline" required><br><br>
                <label for="line_2" style="display: inline; padding-right: 30px">Line 2</label>
                <input type="text" id="line_2" name="line_2" style="display: inline"><br><br>
                <label for="town_or_city" style="display: inline">Town/City</label>
                <input type="text" id="town_or_city" name="town_or_city" style="display: inline" required><br><br>
                <label for="county" style="display: inline; padding-right: 30px">County</label>
                <input type="text" id="county" name="county" style="display: inline" required><br><br>
                <label for="postcode" style="display: inline; padding-right: 10px">Postcode</label>
                <input type="text" id="postcode" name="postcode" style="display: inline" required><br><br>
                <input type="hidden" name="order_submitted" value="<?php echo htmlspecialchars(json_encode($data_array)); ?>">
                <input type="hidden" name="form_submitted" value="1">
                <button id="order_button" style="margin-left: 77.5%">Order</button>
        </form>
    </div>
</div>
<div class="content_container" <?php if($art_selected){?>style="display: none" <?php }else {?> style="display: block" <?php }?>>
    <?php
    if ($order_processed){?>
        <div style="text-align: center">
            <p>Thank you, your order has been processed!</p>
            <p>If you would like to order more paintings, please visit our <a href="index.php">home page!</a></p>
        </div><?php
    }
    else{?>
        <div style="text-align: center">
            <p>Shopping Cart empty.</p>
            <p>Please visit our <a href="index.php">home page</a>, to add as many paintings to your cart as needed.</p>
        </div><?php
    }
    ?>
</div>

<script>
    document.getElementById('order_button').addEventListener('click', function(event) {
        let name = document.getElementById('name').value.trim();
        if (name === "") {
            alert('Please enter your name.');
            event.preventDefault();
            return;
        }

        let phone = document.getElementById('phone_number').value.trim();
        let phone_regex = /^\d{10,11}$/;
        if (!phone_regex.test(phone)) {
            alert('Please enter a valid uk phone number (10-11 digits).');
            event.preventDefault();
            return;
        }

        let email = document.getElementById('email').value.trim();
        let email_regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email_regex.test(email)) {
            alert('Please enter a valid email address.');
            event.preventDefault();
            return;
        }

        let line1 = document.getElementById('line_1').value.trim();
        if (line1 === "") {
            alert('Please enter your address.');
            event.preventDefault();
            return;
        }

        let town_or_city = document.getElementById('town_or_city').value.trim();
        if (town_or_city === "") {
            alert('Please enter your town/city.');
            event.preventDefault();
            return;
        }

        let county = document.getElementById('county').value.trim();
        if (county === "") {
            alert('Please enter your county.');
            event.preventDefault();
            return;
        }

        let postcode = document.getElementById('postcode').value.trim();
        let postcode_regex = /(GIR 0AA)|((^[A-Za-z]{1,2}[0-9][0-9A-Za-z]?)|(^([A-Za-z][A-Ha-hJ-Yj-y][0-9]{1,2})$)|(^([A-Za-z][0-9][A-Za-z])$)|(^([A-Za-z][A-Ha-hJ-Yj-y][0-9][A-Za-z]?)$))\s?[0-9][A-Za-z]{2}/;
        if (!postcode_regex.test(postcode)) {
            alert('Please enter a valid postcode.');
            event.preventDefault();
        }
    });

    function validate_form() {
        let name = document.getElementById('name').value;
        let phone_number = document.getElementById('phone_number').value;
        let line_1 = document.getElementById('line_1').value;
        let line_2 = document.getElementById('line_2').value;
        let town_or_city = document.getElementById('town_or_city').value;
        let county = document.getElementById('county').value;
        let postcode = document.getElementById('postcode').value;

        const regex = /^[a-zA-Z0-9\s.,']+$/;

        if (!regex.test(name) || !regex.test(phone_number) || !regex.test(line_1) || !regex.test(town_or_city) || !regex.test(county) || !regex.test(postcode)) {
            alert("Invalid input. Please avoid special characters.");
            return false;
        }
        if ((line_2.trim() !== "") && !regex.test(line_2)) {
            alert("Invalid input in Line 2. Please avoid special characters.");
            return false;
        }
        return true;
    }

</script>
</body>
<?php
$connect->close();
?>
<footer style="margin-top: auto">
    &copy; 2023 Cara's Art Shop
</footer>
</html>