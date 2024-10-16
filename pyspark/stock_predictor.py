from pyspark.sql import SparkSession
from pyspark.ml.feature import VectorAssembler
from pyspark.ml.regression import RandomForestRegressor
from pyspark.ml.evaluation import RegressionEvaluator
from pyspark.sql.functions import col, lag, datediff, lit, min
from pyspark.sql.window import Window
import matplotlib.pyplot as plt
import pandas as pd
import sys

def prepare_data(spark, file_path):
    # Read the CSV file
    df = spark.read.csv(file_path, header=True, inferSchema=True)

    # Check if the dataframe is empty
    if df.count() == 0:
        raise ValueError("The CSV file is empty or contains no valid data.")

    # Check if 'date' column exists and contains valid data
    if 'date' not in df.columns or df.filter(col('date').isNotNull()).count() == 0:
        raise ValueError("The 'date' column is missing or contains no valid data.")

    # Create lag features
    window = Window.orderBy('date')
    for i in range(1, 6):
        df = df.withColumn(f'close_lag_{i}', lag('close', i).over(window))

    # Calculate returns
    df = df.withColumn('daily_return', (col('close') - col('open')) / col('open'))

    # Calculate days since start
    min_date = df.select(min(col('date').cast('date'))).collect()[0][0]
    if min_date is None:
        raise ValueError("Unable to determine the minimum date. Check the 'date' column for valid values.")
    
    df = df.withColumn('days_since_start', datediff(col('date').cast('date'), lit(min_date)))

    # Drop rows with null values
    df = df.na.drop()

    return df

def train_model(df):
    # Prepare features
    feature_cols = ['open', 'high', 'low', 'close', 'volume', 'daily_return', 'days_since_start'] + [f'close_lag_{i}' for i in range(1, 6)]
    assembler = VectorAssembler(inputCols=feature_cols, outputCol='features')
    data = assembler.transform(df).select('date', 'features', col('close').alias('label'))

    # Split data
    train_data, test_data = data.randomSplit([0.8, 0.2], seed=42)

    # Train model
    rf = RandomForestRegressor(featuresCol='features', labelCol='label', numTrees=100)
    model = rf.fit(train_data)

    # Make predictions
    predictions = model.transform(test_data)

    # Evaluate model
    evaluator = RegressionEvaluator(labelCol='label', predictionCol='prediction', metricName='rmse')
    rmse = evaluator.evaluate(predictions)
    print(f'Root Mean Squared Error (RMSE) on test data = {rmse}')

    return predictions

def plot_predictions(predictions):
    # Convert to Pandas for plotting
    pandas_df = predictions.select('date', 'label', 'prediction').toPandas()
    pandas_df['date'] = pd.to_datetime(pandas_df['date'])
    pandas_df = pandas_df.sort_values('date')

    plt.figure(figsize=(12, 6))
    plt.plot(pandas_df['date'], pandas_df['label'], label='Actual')
    plt.plot(pandas_df['date'], pandas_df['prediction'], label='Predicted')
    plt.title('Stock Price Prediction')
    plt.xlabel('Date')
    plt.ylabel('Price')
    plt.legend()
    plt.savefig('stock_prediction_plot.png')
    plt.close()

if __name__ == '__main__':

    if len(sys.argv) != 2:
        print("Usage: python stock_predictor.py <ticker>")
        sys.exit(1)

    ticker = sys.argv[1]
    spark = SparkSession.builder.appName('StockPredictor').getOrCreate()

    file_path = f'{ticker}_data.csv'  # Use the ticker to construct the file path
    try:
        df = prepare_data(spark, file_path)
        predictions = train_model(df)
        plot_predictions(predictions)
    except ValueError as e:
        print(f"Error: {e}")
        print("Please check your CSV file and ensure it contains valid data.")
    except Exception as e:
        print(f"An unexpected error occurred: {e}")
    finally:
        spark.stop()
