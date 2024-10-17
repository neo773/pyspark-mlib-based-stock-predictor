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
                <ul id="suggestions" class="bg-white border border-gray-300 mt-1 rounded-md shadow-lg hidden"></ul>
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

    <script>
        const tickerInput = document.getElementById('ticker');
        const suggestionsList = document.getElementById('suggestions');

        tickerInput.addEventListener('input', debounce(fetchSuggestions, 300));

        function debounce(func, delay) {
            let timeoutId;
            return function (...args) {
                clearTimeout(timeoutId);
                timeoutId = setTimeout(() => func.apply(this, args), delay);
            };
        }

        async function fetchSuggestions() {
            const query = tickerInput.value.trim();
            if (query.length < 1) {
                suggestionsList.innerHTML = '';
                suggestionsList.classList.add('hidden');
                return;
            }

            <?php
            $env = parse_ini_file('.env');
            $apiKey = $env['ALPHA_VANTAGE_API_KEY'] ?? 'YOUR_API_KEY';
            echo "const API_KEY = '$apiKey';";
            ?>

            try {
                const response = await fetch(`https://www.alphavantage.co/query?function=SYMBOL_SEARCH&keywords=${query}&apikey=${API_KEY}`);
                const data = await response.json();
                
                if (data.bestMatches) {
                    displaySuggestions(data.bestMatches);
                }
            } catch (error) {
                console.error('Error fetching suggestions:', error);
            }
        }

        function displaySuggestions(matches) {
            suggestionsList.innerHTML = '';
            if (matches.length > 0) {
                matches.forEach(match => {
                    const li = document.createElement('li');
                    li.textContent = `${match['1. symbol']} - ${match['2. name']}`;
                    li.classList.add('p-2', 'hover:bg-gray-100', 'cursor-pointer');
                    li.addEventListener('click', () => selectSuggestion(match['1. symbol']));
                    suggestionsList.appendChild(li);
                });
                suggestionsList.classList.remove('hidden');
            } else {
                suggestionsList.classList.add('hidden');
            }
        }

        function selectSuggestion(symbol) {
            tickerInput.value = symbol;
            suggestionsList.classList.add('hidden');
        }

        document.addEventListener('click', (e) => {
            if (!suggestionsList.contains(e.target) && e.target !== tickerInput) {
                suggestionsList.classList.add('hidden');
            }
        });
    </script>
</body>
</html>
