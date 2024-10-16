import yfinance as yf
import pandas as pd
from datetime import datetime, timedelta
import sys

def fetch_stock_data(ticker, start_date=None, end_date=None):
    if start_date is None:
        start_date = datetime.now() - timedelta(days=5*365)  # 5 years of data
    if end_date is None:
        end_date = datetime.now()

    stock = yf.Ticker(ticker)
    df = stock.history(start=start_date, end=end_date)
    df.reset_index(inplace=True)
    df.rename(columns={'Date': 'date', 'Open': 'open', 'High': 'high', 'Low': 'low', 'Close': 'close', 'Volume': 'volume'}, inplace=True)
    return df

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Please provide a ticker symbol as an argument.")
        sys.exit(1)
    
    ticker = sys.argv[1]
    df = fetch_stock_data(ticker)
    df.to_csv(f'{ticker}_data.csv', index=False)
    print(f'Data for {ticker} has been saved to {ticker}_data.csv')
