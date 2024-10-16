<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Predictor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold mb-8 text-center">Stock Predictor</h1>
        <form action="predict.php" method="post" class="max-w-md mx-auto">
            <div class="mb-4">
                <label for="ticker" class="block text-gray-700 text-sm font-bold mb-2">Stock Ticker:</label>
                <input type="text" id="ticker" name="ticker" required
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" name="predict"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Predict
                </button>
            </div>
        </form>
        <div id="result" class="mt-8">
            <?php
            if (isset($_GET['prediction'])) {
                echo '<h2 class="text-2xl font-bold mb-4">Prediction Result</h2>';
                echo '<img src="stock_prediction_plot.png" alt="Stock Prediction Plot" class="mx-auto">';
            }
            ?>
        </div>
    </div>
</body>
</html>
