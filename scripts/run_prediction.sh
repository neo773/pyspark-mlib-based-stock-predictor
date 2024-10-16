#!/bin/bash

# Activate virtual environment if it exists
if [ -d ".venv/bin" ]; then
    source .venv/bin/activate
else
    echo "Virtual environment not found. Creating one..."
    python -m venv .venv
    source .venv/bin/activate
    pip install pyspark yfinance pandas matplotlib
fi

spark-submit pyspark/stock_predictor.py $1