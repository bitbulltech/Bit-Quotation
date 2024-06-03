<?php
include 'header.php';
include 'config.php';

// Define how many results you want per page
$results_per_page = 30;

// Get current page number
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = $_GET['page'];
} else {
    $page = 1;
}

// Calculate the offset for the query
$offset = ($page - 1) * $results_per_page;
if(isset($_GET)){
	
	foreach($_GET as $k => $gv){
		$_GET[$k]=trim(urldecode($gv));
	}
}
// Search query parameters
$search_quotation_number = isset($_GET['q_quotation_number']) ? $_GET['q_quotation_number'] : '';
$search_email = isset($_GET['q_email']) ? $_GET['q_email'] : '';
$search_phone = isset($_GET['q_phone']) ? $_GET['q_phone'] : '';
$search_name = isset($_GET['q_name']) ? $_GET['q_name'] : '';
 
// Prepare SQL query for fetching quotations with search criteria and pagination
$sql = "SELECT * FROM quotations ";
$sqlw=array();
if (!empty($search_quotation_number)) {
    $sqlw[]= " quotation_number LIKE '%$search_quotation_number%'";
}
if (!empty($search_email)) {
    $sqlw[]= " email LIKE '%$search_email%'";
}
if (!empty($search_phone)) {
    $sqlw[]= " phone LIKE '%$search_phone%'";
}
if (!empty($search_name)) {
    $sqlw[]= " name LIKE '%$search_name%'";
}
if(count($sqlw) >= 1){
	$sql .=" WHERE ";
}
$sql .= implode(" OR ",$sqlw)." ORDER BY id DESC LIMIT $offset, $results_per_page";
  
// Execute the query
$result = $conn->query($sql);

// Fetch data and display in a table
if ($result && $result->num_rows > 0) {
    echo '<div class="container">';
    echo '<h2 class="my-4">Quotation List</h2>';
    echo '<div class="mb-3">';
    echo '<form method="GET">';
    echo '<div class="row">';
    echo '<div class="col-md-3">';
    echo '<input type="text" class="form-control" name="q_quotation_number" placeholder="Quotation Number" value="' . htmlspecialchars($search_quotation_number) . '">';
    echo '</div>';
    echo '<div class="col-md-3">';
    echo '<input type="text" class="form-control" name="q_email" placeholder="Email" value="' . htmlspecialchars($search_email) . '">';
    echo '</div>';
    echo '<div class="col-md-3">';
    echo '<input type="text" class="form-control" name="q_phone" placeholder="Phone" value="' . htmlspecialchars($search_phone) . '">';
    echo '</div>';
    echo '<div class="col-md-3">';
    echo '<input type="text" class="form-control" name="q_name" placeholder="Name" value="' . htmlspecialchars($search_name) . '">';
    echo '</div>';
    echo '</div>';
    echo '<button type="submit" class="btn btn-primary mt-2">Search</button>';
    echo ' <a href="add_quotation.php" class="btn btn-success mt-2">Add Quotation</button>';
    echo '</form>';
    echo '</div>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped">';
    echo '<thead>';
    echo '<tr>';
    echo '<th scope="col">Sr. No.</th>';
    echo '<th scope="col">Quotation Number</th>';
    echo '<th scope="col">Name</th>';
    echo '<th scope="col">Phone</th>';
    echo '<th scope="col">Total Amount</th>';
    echo '<th scope="col">Actions</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    
    // Counter for Sr. No.
    $counter = ($page - 1) * $results_per_page + 1;
    
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $counter . '</td>';
        echo '<td>' . $row['quotation_number'] . '</td>';
        echo '<td>' . $row['name'] . '</td>';
        echo '<td>' . $row['phone'] . '</td>';
        // Calculate total amount here if you have the relevant data structure
        echo '<td>'.CURRENCY_PREFIX.'' . $row['total_amount'] . CURRENCY_SUFIX .'</td>'; // Placeholder for total amount
        echo '<td>';
        echo '<a href="view_quotation.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm">View</a> ';
        echo '<a href="edit_quotation.php?id=' . $row['id'] . '" class="btn btn-warning btn-sm">Edit</a> ';
        echo '<a href="javascript:warningDel(`delete_quotation.php?id=' . $row['id'] . '`);" class="btn btn-danger btn-sm">Delete</a> ';
        echo '</td>';
        echo '</tr>';
        $counter++;
    }
    
    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // Pagination
    $sql = "SELECT COUNT(*) AS total FROM quotations WHERE 1=1";
    if (!empty($search_quotation_number)) {
        $sql .= " AND quotation_number LIKE '%$search_quotation_number%'";
    }
    if (!empty($search_email)) {
        $sql .= " AND email LIKE '%$search_email%'";
    }
    if (!empty($search_phone)) {
        $sql .= " AND phone LIKE '%$search_phone%'";
    }
    if (!empty($search_name)) {
        $sql .= " AND name LIKE '%$search_name%'";
    }
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_pages = ceil($row['total'] / $results_per_page);
    echo '<nav aria-label="Page navigation">';
    echo '<ul class="pagination">';
    for ($i = 1; $i <= $total_pages; $i++) {
        echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '&q_quotation_number=' . htmlspecialchars($search_quotation_number) . '&q_email=' . htmlspecialchars($search_email) . '&q_phone=' . htmlspecialchars($search_phone) . '&q_name=' . htmlspecialchars($search_name) . '">' . $i . '</a></li>';
    }
    echo '</ul>';
    echo '</nav>';
    echo '</div>';
} else {
    echo '<div class="container">';
    echo '<p>No quotations found.</p>';
    echo '</div>';
}

include 'footer.php';
?>
<script>
function warningDel(url){
	if(confirm("Do you want to delete quotation?")){
		window.location=url;
	}
}
</script>
