<?php
include 'config.php';
$id=$_GET['id'];
$conn->query("delete from quotation_items where quotation_id='$id' ");
$conn->query("delete from quotations where id='$id' ");
   header("location: quotation_list.php");
    exit();             
?>