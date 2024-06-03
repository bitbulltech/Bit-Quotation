<?php
include 'config.php';
include 'header.php';
function getLastQuotationNumber($conn) {
    $sql = "SELECT id FROM quotations ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id']+1;
    } else {
        // If no previous quotation number exists, return 0
        return 1;
    }
}

// Initialize variables
$name = $company_name = $tax_number = $address = $email = $phone = "";
$items = [];
$name_err = $email_err = $phone_err = $item_err = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
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
    // Check for errors before inserting into database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($item_err)) {
        // Insert quotation into database
        $sql = "INSERT INTO quotations (quotation_number, name, company_name, tax_number, address, email, phone, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $qn=QUTATION_PREFIX . "".getLastQuotationNumber($conn);
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssss", $qn, $param_name, $param_company_name, $param_tax_number, $param_address, $param_email, $param_phone,$remarks);
            
            $param_name = $name;
            $param_company_name = trim($_POST["company_name"]);
            $param_tax_number = trim($_POST["tax_number"]);
            $param_address = trim($_POST["address"]);
            $param_email = $email;
            $param_phone = $phone;
            $total_amount=0;
            if ($stmt->execute()) {
                $quotation_id = $stmt->insert_id;
                
                $sql_item = "INSERT INTO quotation_items (quotation_id, item_name, description, unit_price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)";
                if ($stmt_item = $conn->prepare($sql_item)) {
                    foreach ($items as $item) {
						$total= $item["unit_price"] * $item["quantity"];
						$total_amount +=$total;
                        $stmt_item->bind_param("issdis", $quotation_id, $item["name"], $item["description"], $item["unit_price"], $item["quantity"], $total);
                        $stmt_item->execute();
                    }
                }
				$conn->query("update quotations set total_amount='$total_amount' where id='$quotation_id' ");
                // Redirect to quotation list
                header("location: quotation_list.php");
                exit();
            } else {
                echo "Something went wrong. Please try again later.";
            }
        }
      if (isset($stmt)) {
            $stmt->close();
        }
    }
    $conn->close();
}
?>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<div class="container">
    <h2 class="my-4">Add Quotation</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" id="name"  class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>"  autocomplete="nope" >
            <span class="invalid-feedback"><?php echo $name_err; ?></span>
        </div>
		<div id="suggestions"></div>
        <div class="form-group">
            <label>Company Name (optional)</label>
            <input type="text" name="company_name" class="form-control" value="<?php echo $company_name; ?>">
        </div>
        <div class="form-group">
            <label>Tax Number (optional)</label>
            <input type="text" name="tax_number" class="form-control" value="<?php echo $tax_number; ?>">
        </div>
        <div class="form-group">
            <label>Address</label>
            <input type="text" name="address" class="form-control" value="<?php echo $address; ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="text" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
            <span class="invalid-feedback"><?php echo $email_err; ?></span>
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $phone; ?>">
            <span class="invalid-feedback"><?php echo $phone_err; ?></span>
        </div>
        <div class="form-group">
            <label>Items</label>
            <div id="item-fields">
                <div class="form-row">
                    <div class="col-md-2">
                        <input type="text" name="items[0][name]" class="form-control" placeholder="Item Name">
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="items[0][description]" class="form-control" placeholder="Description">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[0][unit_price]" class="form-control" placeholder="Unit Price">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[0][quantity]" class="form-control" placeholder="Quantity">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="items[0][total]" class="form-control" placeholder="Total" readonly>
                    </div>
                </div>
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
            <textarea name="remarks" class="form-control " ></textarea>
            
        </div>
		
        <span class="invalid-feedback d-block"><?php echo $item_err; ?></span>
        <div class="form-group mt-4">
            <input type="submit" class="btn btn-primary" value="Submit">
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

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


<script>
$(document).ready(function() {
    $("#name").autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "get_quotation_suggestions.php",
                type: "POST",
                dataType: "json",
                data: { name: request.term },
                success: function(data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function(event, ui) {
            $('input[name="company_name"]').val(ui.item.company_name);
            $('input[name="tax_number"]').val(ui.item.tax_number);
            $('input[name="address"]').val(ui.item.address);
            $('input[name="email"]').val(ui.item.email);
            $('input[name="phone"]').val(ui.item.phone);
            $('#suggestions').empty();
        },
        response: function(event, ui) {
            if (ui.content.length === 0) {
                $('#suggestions').html('');
            } else {
                $('#suggestions').empty();
            }
        }
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        return $("<li>")
            .append("<div>" + item.label + "<br>" + item.company_name + " - " + item.phone + "</div>")
            .appendTo(ul);
    };

    $("#name").on("keydown", function(event) {
        if (event.keyCode === $.ui.keyCode.BACKSPACE) {
            $('#suggestions').empty();
            $('input[name="company_name"]').val('');
            $('input[name="tax_number"]').val('');
            $('input[name="address"]').val('');
            $('input[name="email"]').val('');
            $('input[name="phone"]').val('');
        }
    });
});
</script>
<?php
include 'footer.php';
?>
