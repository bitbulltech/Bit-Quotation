<?php
include 'header.php';

// Database connection
require_once 'config.php';

// Fetch counts from database
$quotation_count = 0;
$invoice_count = 0;
$unpaid_invoice_count = 0;
$paid_invoice_count = 0;

$sql = "SELECT 
            (SELECT COUNT(*) FROM quotations) AS quotation_count,
            (SELECT COUNT(*) FROM invoices) AS invoice_count,
            (SELECT COUNT(*) FROM invoices WHERE status = 'unpaid') AS unpaid_invoice_count,
            (SELECT COUNT(*) FROM invoices WHERE status = 'paid') AS paid_invoice_count";

if($result = $conn->query($sql)){
    if($row = $result->fetch_assoc()){
        $quotation_count = $row['quotation_count'];
        $invoice_count = $row['invoice_count'];
        $unpaid_invoice_count = $row['unpaid_invoice_count'];
        $paid_invoice_count = $row['paid_invoice_count'];
    }
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>

<div class="container">
    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Quotations</h5>
                    <p class="card-text"><?php echo $quotation_count; ?></p>
                </div>
            </div>
        </div>
 <!--
	<div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Invoices</h5>
                    <p class="card-text"><?php echo $invoice_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Unpaid Invoices</h5>
                    <p class="card-text"><?php echo $unpaid_invoice_count; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Paid Invoices</h5>
                    <p class="card-text"><?php echo $paid_invoice_count; ?></p>
                </div>
            </div>
        </div>
    -->
	</div>
</div>

<?php
include 'footer.php';
