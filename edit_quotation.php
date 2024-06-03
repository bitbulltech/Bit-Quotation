<?php
include 'config.php';
include 'header.php';

// Initialize variables
$name = $company_name = $tax_number = $address = $email = $phone = "";
$items = [];
$name_err = $email_err = $phone_err = $item_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

$id = $_GET['id'];

// Validate inputs (similar to add_quotation.php)
if (empty(trim($_POST["name"]))) {
    $name_err = "Please enter the name.";
} else {
    $name = trim($_POST["name"]);
}

if (empty(trim($_POST["email"]))) {
    $email_err = "Please enter the email.";
} else {
    $email = trim($_POST["email"]);
}

if (empty(trim($_POST["phone"]))) {
    $phone_err = "Please enter the phone number.";
} else {
    $phone = trim($_POST["phone"]);
}

if (!empty($_POST["items"])) {
    foreach ($_POST["items"] as $item) {
        if (empty(trim($item["name"])) || empty(trim($item["unit_price"])) || empty(trim($item["quantity"]))) {
            $item_err = "Please fill in all item fields.";
            break;
        }
        $items[] = $item;
    }
} else {
    $item_err = "Please add at least one item.";
}

$remarks=$_POST['remarks'];
// Check for errors before updating the database
if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($item_err)) {
// Update quotation in the database
// Construct SQL statement for updating the quotation details
$sql_update_quotation = "UPDATE quotations SET name = '" . $name . "', company_name = '" . trim($_POST["company_name"]) . "', remarks = '" . trim($_POST["remarks"]) . "', tax_number = '" . trim($_POST["tax_number"]) . "', address = '" . trim($_POST["address"]) . "', email = '" . $email . "', phone = '" . $phone . "' WHERE id = '" . $id . "'";
 
// Execute the update statement for the quotation
if ($conn->query($sql_update_quotation) === TRUE) {
	$conn->query("delete from quotation_items  where quotation_id='$id' ");
                
    // Update quotation items
                $sql_item = "INSERT INTO quotation_items (quotation_id, item_name, description, unit_price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_item = $conn->prepare($sql_item)) {
                    foreach ($items as $item) {
						$total= $item["unit_price"] * $item["quantity"];
						$total_amount +=$total;
                        $stmt_item->bind_param("issdis", $id, $item["name"], $item["description"], $item["unit_price"], $item["quantity"], $total);
                        $stmt_item->execute();
                    }
                }
				$conn->query("update quotations set total_amount='$total_amount' where id='$id' ");
                // Redirect to quotation list
                

    // Redirect to quotation list after successful update
    header("location: quotation_list.php");
    exit();
} else {
    echo "Error updating quotation: " . $conn->error;
}

}





}  
    // Retrieve quotation details from the database based on ID
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $id = $_GET['id'];

        // Prepare and execute SQL query to retrieve quotation details
        $sql = "SELECT * FROM quotations WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    // Assign retrieved data to variables
                    $name = $row['name'];
                    $company_name = $row['company_name'];
                     $remarks = $row['remarks'];
                    $tax_number = $row['tax_number'];
                    $address = $row['address'];
                    $email = $row['email'];
                    $phone = $row['phone'];
                    // Additional processing if needed
                } else {
                    echo "Quotation not found.";
                }
            } else {
                echo "Error executing query: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing query: " . $conn->error;
        }
    } else {
        echo "Invalid quotation ID.";
    }

?>

<div class="container">
    <h2 class="my-4">Edit Quotation</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?id=<?php echo $id;?>" method="post">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($name); ?>">
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
        <div class="form-group">
            <label>Company Name (optional)</label>
            <input type="text" name="company_name" class="form-control" value="<?php echo htmlspecialchars($company_name); ?>">
        </div>
        <div class="form-group">
            <label>Tax Number (optional)</label>
            <input type="text" name="tax_number" class="form-control" value="<?php echo htmlspecialchars($tax_number); ?>">
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($address); ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($email); ?>">
            <span class="invalid-feedback"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo htmlspecialchars($phone); ?>">
            <span class="invalid-feedback"><?php echo $phone_err; ?></span>
        </div>

<div class="form-group">
    <label>Items</label>
    <div id="item-fields">
        <?php
        // Retrieve existing items for the quotation
        $sql_existing_items = "SELECT * FROM quotation_items WHERE quotation_id = ?";
        if ($stmt_existing_items = $conn->prepare($sql_existing_items)) {
            $stmt_existing_items->bind_param("i", $id);
            $stmt_existing_items->execute();
            $result_existing_items = $stmt_existing_items->get_result();
            while ($row_existing_item = $result_existing_items->fetch_assoc()) {
                // Output existing item fields
                echo '<div class="form-row mt-2">';
                echo '<div class="col-md-2">';
                echo '<input type="text" name="items[' . $row_existing_item['id'] . '][name]" class="form-control" value="' . htmlspecialchars($row_existing_item['item_name']) . '" placeholder="Item Name">';
                echo '</div>';
                echo '<div class="col-md-4">';
                echo '<input type="text" name="items[' . $row_existing_item['id'] . '][description]" class="form-control" value="' . htmlspecialchars($row_existing_item['description']) . '" placeholder="Description">';
                echo '</div>';
                echo '<div class="col-md-2">';
                echo '<input type="number" name="items[' . $row_existing_item['id'] . '][unit_price]" class="form-control" value="' . htmlspecialchars($row_existing_item['unit_price']) . '" placeholder="Unit Price">';
                echo '</div>';
                echo '<div class="col-md-2">';
                echo '<input type="number" name="items[' . $row_existing_item['id'] . '][quantity]" class="form-control" value="' . htmlspecialchars($row_existing_item['quantity']) . '" placeholder="Quantity">';
                echo '</div>';
                echo '<div class="col-md-2">';
                echo '<input type="number" name="items[' . $row_existing_item['id'] . '][total]" class="form-control" value="' . htmlspecialchars($row_existing_item['total']) . '" placeholder="Total" readonly>';
                echo '</div>';
                echo '</div>';
            }
            $stmt_existing_items->close();
        }
        ?>
    </div>
            <div id="item-fields2">
                <div class="form-row">
                    <div class="col-md-10 mt-2">
                       <button type="button" class="btn btn-secondary mt-3" id="add-item-btn">Add More Items</button>
                    </div>
                    <div class="col-md-2 mt-2">
                        <input type="number"  id="tn"  class="form-control" placeholder="Grand Total" readonly>
                    </div>
                </div>
            </div>
</div>
        <div class="form-group">
            <label>Remarks</label>
            <textarea name="remarks" class="form-control " ><?php echo htmlspecialchars($remarks); ?></textarea>
            
        </div>

        <span class="invalid-feedback d-block"><?php echo $item_err; ?></span>
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Submit">
        </div>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script>
$(document).ready(function() {
    let itemIndex = 1;
    $('#add-item-btn').click(function() {
        $('#item-fields').append(`
            <div class="form-row mt-2">
                <div class="col-md-2">
                    <input type="text" name="items[${itemIndex}][name]" class="form-control" placeholder="Item Name">
                </div>
                <div class="col-md-4">
                    <input type="text" name="items[${itemIndex}][description]" class="form-control" placeholder="Description">
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[${itemIndex}][unit_price]" class="form-control" placeholder="Unit Price">
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Quantity">
                </div>
                <div class="col-md-2">
                    <input type="number" name="items[${itemIndex}][total]" class="form-control" placeholder="Total" readonly>
                </div>
            </div>
        `);
        itemIndex++;
    });
});
</script>
<script>
 

$(document).ready(function() {
    // Function to calculate total price for an item
    function calculateTotalPrice(itemIndex) {
        var unitPrice = parseFloat($(`input[name='items[${itemIndex}][unit_price]']`).val()) || 0;
        var quantity = parseFloat($(`input[name='items[${itemIndex}][quantity]']`).val()) || 0;
        var total = unitPrice * quantity;
        $(`input[name='items[${itemIndex}][total]']`).val(total.toFixed(2));
		calculateGrandTotal();
    }
    function calculateGrandTotal() {
        var grandTotal = 0;
        $('[name^="items["][name$="[total]"]').each(function() {
            var itemTotal = parseFloat($(this).val()) || 0;
            grandTotal += itemTotal;
        });
        $('#tn').val(grandTotal.toFixed(2));
    }
	
    // Calculate total price for each item when unit price or quantity changes
    $(document).on('input', '[name^="items["][name$="[unit_price]"], [name^="items["][name$="[quantity]"]', function() {
        var itemIndex = $(this).attr('name').match(/\[(\d+)\]/)[1];
        calculateTotalPrice(itemIndex);
       calculateGrandTotal();
    });
});
</script>

<?php include 'footer.php'; ?>
