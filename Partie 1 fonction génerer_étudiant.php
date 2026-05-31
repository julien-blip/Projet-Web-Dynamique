<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'db_connect.php';

mysqli_begin_transaction($conn);

 "Active les erreurs, charge la BDD, démarre la transaction"
