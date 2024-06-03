<?php
include 'config.php';

// Check if ID parameter is set
if (isset($_GET['id'])) {
    $id = base64_decode($_GET['id'])/100;
 
    // Retrieve quotation details from the database based on ID
    $sql = "SELECT * FROM quotations WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                // Display quotation details
                ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

                <div class="container">
                    <div class="row">
                        <div class="col-12 text-center">
							<h2>Quotation</h2>
						</div>
					</div>
					<div class="row">
                        <div class="col-6">
                             
                            <p><b>Date/Time: <?php echo htmlspecialchars($row['quotation_date']); ?></b><br>
                            <br><?php echo COMPANY; ?><br>
							<?php echo ADDRESS; ?><br>
                            <?php echo CONTACT_EMAIL; ?><br>
                            <?php echo PHONE; ?><br>
                            <?php echo TAXNO; ?></p>
                        </div>
                        <div class="col-6 text-right">
                            <b>Quotation To</b>
                            <p>#<?php echo htmlspecialchars($row['quotation_number']); ?><br>
                            <?php if($row['company_name']!=""){ echo htmlspecialchars($row['company_name']); echo "<br>"; } ?>
                            <?php echo htmlspecialchars($row['name']); ?><br>
                            <?php echo htmlspecialchars($row['address']); ?><br>
                            <?php echo htmlspecialchars($row['email']); ?><br>
                            <?php echo htmlspecialchars($row['phone']); ?><br>
                            Tax No. <?php echo htmlspecialchars($row['tax_number']); ?></p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Unit Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                  <tbody>
    <?php
    // Retrieve quotation items from the database based on quotation ID
    $sql_items = "SELECT * FROM quotation_items WHERE quotation_id = ?";
    if ($stmt_items = $conn->prepare($sql_items)) {
        $stmt_items->bind_param("i", $id);
        if ($stmt_items->execute()) {
            $result_items = $stmt_items->get_result();
            while ($row_item = $result_items->fetch_assoc()) {
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row_item['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($row_item['description']); ?></td>
                    <td><?php echo  htmlspecialchars($row_item['unit_price']) ; ?></td>
                    <td><?php echo htmlspecialchars($row_item['quantity']); ?></td>
                    <td><?php echo  htmlspecialchars($row_item['total']) ; ?></td>
                </tr>
                <?php
            }
        } else {
            echo "Error executing query: " . $stmt_items->error;
        }
        $stmt_items->close();
    } else {
        echo "Error preparing query: " . $conn->error;
    }
    ?>
 

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-right">
                            <h4>Grand Total: <?php echo CURRENCY_PREFIX . htmlspecialchars($row['total_amount']) . CURRENCY_SUFIX; ?></h4>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <b>Remarks</b>
                            <p><?php echo htmlspecialchars($row['remarks']);?></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <b>Terms and Conditions:</b>
                            <p><?php echo TERMS;?></p>
                        </div>
                    </div>
                </div>
                <?php
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

<div class="container mt-4 no-print">
    <button class="btn btn-primary no-print" onclick="window.print()">Print</button>
	<a href="javascript:copyUrl();" class="btn btn-dark">Copy Quotation URL (link) to share</a><br><br>
	<input type="text" id="textToCopy" class="form-control" value="<?php echo BASE_URL.'/quotation.php?id='.base64_encode($id*100);?>" onclick="copyUrl();" readonly >
</div>

<script>
function copyUrl(){
	var textToCopy = document.getElementById("textToCopy");;
            textToCopy.select();
            document.execCommand('copy');
            alert("URL copied ");
}
</script>
<?php include 'footer.php'; ?>
