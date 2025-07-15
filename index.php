<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="website_styling.css">
    <title>Cara's Shopfront</title>
    <style>
        .framed_image{
            min-width: 225px;
            height: 300px;
            width: 250px;
            display: block;
            border: 2px solid brown;
        }
        .item_grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .item {
            display: flex;
            text-align: center;
            flex-direction: column;
            align-items: center;
            flex: 0 0 calc(33.33% - 10px);
            margin-right: 10px;
        }
        .banner {
            display: flex;
            justify-content: space-between;
        }
        .banner img {
            width: 33.3%;
            height: auto;
            max-height: 250px;
            object-fit: cover;
            padding: 5px;
        }
        #cart_button {
            position: fixed;
            bottom: 0;
            right: 0;
            margin-bottom: 20px;
            z-index: 999;
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

$sql = "SELECT * FROM ArtDb WHERE Sold = 0";
$result = mysqli_query($connect, $sql);
$total_items = $result->num_rows;
$items_per_page = 12;
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;
$sql = "SELECT * FROM ArtDb WHERE Sold = 0 LIMIT $offset, $items_per_page";
$result = mysqli_query($connect, $sql);
?>

<header>
    <div class="banner">
        <img src="https://images.unsplash.com/photo-1460661419201-fd4cecdf8a8b?auto=format&fit=crop&q=80&w=1160&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1cGFnZXB8fHxlbmV8fHx8fHw=" alt="Image 1" width="15%" height="15%">
        <img src="https://images.unsplash.com/photo-1602465605153-a40a52556990?auto=format&fit=crop&q=80&w=1035&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Image 2" width="15%" height="15%">
        <img src="https://images.unsplash.com/photo-1480355781839-51097c7d4f9f?auto=format&fit=crop&q=80&w=987&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Image 3" width="15%" height="15%">
    </div>
    <h1 style="color: black">Cara's Art Shop</h1>
</header>
<body>
<br><br>

<div style="text-align: center; margin-left: 10%; margin-right: 10%;">
    <div class="item_grid">
        <?php
        while($row = $result->fetch_assoc()) {
            $image_data = $row['Image'];
            $image_name = $row['Name'];
            $image_id = $row['Art_ID'];
            $image_price = $row['Price'];
            $image_description = $row['Description'];
            $image_type = 'image/jpeg';
            if (!($row['Sold'])){
                ?>
                <div class="item">
                    <?php echo "<img class='framed_image' src='data:$image_type;base64,$image_data' alt='$image_description'>"; ?><br>
                    <span style="display: inline"><?php echo $image_name?></span><br>
                    <span style="display: inline">£<?php echo $image_price?></span><br>
                    <input type="hidden" name="hidden_art_id" value="<?php echo $image_id ?>">
                    <button class="add_or_remove_button" data-art-id="<?php echo $image_id ?>" style="margin-top: auto">Add to Cart</button><br><br>
                </div>
                <?php
            }
        }
        $connect->close();
        ?>
    </div>
</div>

<div style="text-align: center">
    <?php
    for ($i = 1; $i <= $total_pages; $i++) {
        echo "<button class='page_links'>$i</button>";
    }
    $start_item = min($offset + 1, $total_items);
    $end_item = min($offset + $items_per_page, $total_items);
    echo "<br><p>Showing $start_item-$end_item of $total_items results</p>";

    $data_array =[];
    if (isset($_GET['data']) && isset($_GET['page'])) {
        $data_array_encoded = $_GET['data'];
        $data_array_JSON = base64_decode($data_array_encoded);
        $data_array = json_decode(urldecode($data_array_JSON), true);
    }

    ?>
</div>

<div id="cart_button" class="custom_button" style="padding-bottom: 2px; padding-top: 2px; width: 40px; margin-left: 20px; background-color: lightgray">
    <img src="https://www.goodfreephotos.com/cache/vector-images/shopping-cart-vector-clipart.png" alt="Pixelart shopping cart" width="40px" height="50px">
    <span id="cart_counter_id" style="position: absolute; top: 5px; right: 5px; background-color: red; color: white; border-radius: 50%; padding: 2px 6px; font-size: 12px; display: none">1</span>
</div>
<br><br>

<script>
    let selected_art_ids = [];
    let received_data = <?php echo json_encode($data_array); ?>;
    const cart_counter_show = document.getElementById("cart_counter_id");
    const cart_button = document.getElementById("cart_button");
    const add_or_remove_buttons = document.querySelectorAll('.add_or_remove_button');

    document.addEventListener('DOMContentLoaded', function() {
        if (received_data.length > 0) {
            selected_art_ids = received_data;
            cart_counter_show.style.display = "block";
            cart_counter_show.textContent = String(selected_art_ids.length);
            for (let i = 0; i < selected_art_ids.length; i++) {
                let current_art_id = selected_art_ids[i];
                let button_update = document.querySelector('.add_or_remove_button[data-art-id="' + current_art_id + '"]');
                if (button_update) {
                    button_update.textContent = 'Remove from Cart';
                }
            }
        }

        function handle_button_click(art_id) {
            console.log(art_id);
            let index = selected_art_ids.indexOf(art_id);
            if (index === -1) {
                selected_art_ids.push(art_id);
            } else {
                selected_art_ids.splice(index, 1);
            }
            console.log("Selected Art IDs:", selected_art_ids);
        }

        function add_cart_display() {
            let current_cart_item_value = parseInt(cart_counter_show.textContent);
            let new_cart_item_value = current_cart_item_value + 1;
            if(cart_counter_show.style.display === "none"){
                cart_counter_show.style.display = "block";
            }
            else{
                cart_counter_show.textContent = String(new_cart_item_value);
            }
        }

        function remove_cart_display() {
            let current_cart_value = parseInt(cart_counter_show.textContent);
            if (current_cart_value === 1){
                cart_counter_show.style.display = "none";
            }
            else {
                let new_cart_value = current_cart_value - 1;
                cart_counter_show.textContent = String(new_cart_value);
            }
        }

        add_or_remove_buttons.forEach(function(add_or_remove_button) {
            add_or_remove_button.addEventListener('click', function(event) {
                let clicked_button = event.target;
                let container_data = clicked_button.getAttribute('data-art-id');
                let button_text = clicked_button.textContent;
                if(button_text === "Add to Cart"){
                    add_cart_display();
                    clicked_button.textContent = "Remove from Cart";
                }
                else{
                    remove_cart_display();
                    clicked_button.textContent = "Add to Cart";
                }
                handle_button_click(container_data);
            });
        });

        const page_links = document.querySelectorAll('.page_links');

        page_links.forEach(function(page_link) {
            page_link.addEventListener('click', function(event) {
                let clicked_button = event.target;
                let button_text = clicked_button.textContent;
                let data_array_JSON = encodeURIComponent(JSON.stringify(selected_art_ids));
                let encrypted_data = btoa(data_array_JSON);
                window.location.href = "index.php?page="+button_text+"&data=" + encrypted_data;
            });
        });

        cart_button.addEventListener("click", function() {
            let data_array_JSON = encodeURIComponent(JSON.stringify(selected_art_ids));
            let encrypted_data = btoa(data_array_JSON);
            window.location.href = "order.php?&data=" + encrypted_data;
        });
    });
</script>

</body>
<footer style="margin-top: auto">
    &copy; 2023 Cara's Art Shop
</footer>
</html>