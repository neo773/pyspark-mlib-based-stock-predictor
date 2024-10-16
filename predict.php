<?php
if (isset($_POST['predict'])) {
    $ticker = $_POST['ticker'];
    
    // Fetch data
    exec("sh ./scripts/run_fetch_data.sh $ticker", $output, $return_var);
    if ($return_var !== 0) {
        die("Error fetching data: " . implode("\n", $output));
    }

    // Run prediction
    exec("sh ./scripts/run_prediction.sh $ticker", $output, $return_var);
    if ($return_var !== 0) {
        die("Error running prediction: " . implode("\n", $output));
    }

    // Redirect to show results
    header("Location: index.php?prediction=1");
    exit();
}
?>