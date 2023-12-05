CREATE TEMP TABLE national_avg_price (
  month_period INT64,
  zip_code STRING,
  zip3 INT64,
  mfg_name STRING,
  ipm_id STRING,
  distance INT64,
  size_code STRING,
  count_unique_stores INT64,
  total_units_sold FLOAT64,
  unit_retail_normalized FLOAT64,
  margin_dollar_normalized FLOAT64
);

FOR lookback IN (
  SELECT lookback_period
  FROM UNNEST ([1, 3, 6, 12]) AS lookback_period
) DO
INSERT INTO national_avg_price WITH TXN_ZIP AS (
    SELECT TXN.customer_zipcode,
      TXN.org_unit_id,
      IPM.mfg_name,
      IPM.size_code,
      TXN.ipm_id,
      TT.market_insights_tire_type,
      SUM(units_sold) AS total_units_sold,
      SUM(ext_retail) AS total_retail,
      SUM(ext_retail) / SUM(units_sold) AS volume_weighted_unit_retail,
      (SUM(ext_retail) - SUM(ext_cost)) / SUM(units_sold) AS volume_weighted_margin,
      SUM(ext_cost) AS total_unit_cost,
      COUNT(TXN.date) AS count_observations
    FROM #GCP_PROJECT_ID.#CC_DATASET.transactions_12 TXN
      LEFT JOIN #GCP_PROJECT_ID.ipm.industry_product_master IPM ON IPM.ipm_id = TXN.ipm_id
      LEFT JOIN #GCP_PROJECT_ID.reporting_datasets.tire_types TT ON TT.tire_type = IPM.tire_type
    WHERE TXN.date > DATE_SUB(
        CURRENT_DATE(),
        INTERVAL lookback.lookback_period MONTH
      )
      AND vendor_id != 9999
    GROUP BY TXN.customer_zipcode,
      IPM.mfg_name,
      IPM.size_code,
      TXN.ipm_id,
      TXN.org_unit_id,
      TT.market_insights_tire_type
  ),
  ZIP_TABLE AS (
    SELECT ZIP_TABLE.zip_code,
      CAST(SUBSTR(ZIP_TABLE.zip_code, 0, 3) AS INT) AS zip3,
      store_count,
      zip_count,
      candidate_zip,
      pass_5store_check,
      pass_10store_check,
      distance
    FROM #GCP_PROJECT_ID.reporting_datasets.zip_radius ZIP_TABLE
      CROSS JOIN UNNEST (zip_array) AS candidate_zip
    WHERE snapshot_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)
      AND snapshot_date = (
        SELECT MAX(snapshot_date)
        FROM #GCP_PROJECT_ID.reporting_datasets.zip_radius
        WHERE snapshot_date >= DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)
      )
  ),
  FINAL_POS_TABLE AS (
    SELECT ZIP_TABLE.zip_code,
      ZIP_TABLE.zip3,
      ZIP_TABLE.zip_count,
      ZIP_TABLE.distance,
      ZIP_TABLE.store_count,
      TXN_ZIP.org_unit_id,
      pass_5store_check,
      pass_10store_check,
      TXN_ZIP.mfg_name,
      TXN_ZIP.ipm_id,
      TXN_ZIP.size_code,
      TXN_ZIP.market_insights_tire_type,
      TXN_ZIP.count_observations,
      total_units_sold,
      total_retail,
      total_unit_cost,
      volume_weighted_unit_retail,
      volume_weighted_margin
    FROM ZIP_TABLE
      LEFT JOIN TXN_ZIP ON TXN_ZIP.customer_zipcode = ZIP_TABLE.candidate_zip
    WHERE org_unit_id is not null
  ),
  WEIGHT_TABLE AS (
    SELECT zip_code,
      zip3,
      mfg_name,
      ipm_id,
      distance,
      size_code,
      org_unit_id,
      total_units_sold,
      total_retail,
      SUM(total_units_sold) OVER(
        PARTITION BY zip_code,
        zip3,
        mfg_name,
        ipm_id,
        distance,
        size_code
      ) AS total_units_per_group,
      total_units_sold / SUM(total_units_sold) OVER(
        PARTITION BY zip_code,
        zip3,
        mfg_name,
        ipm_id,
        distance,
        size_code
      ) AS weight,
      volume_weighted_unit_retail,
      volume_weighted_margin,
      COUNT(*) OVER (
        PARTITION BY zip_code,
        zip3,
        mfg_name,
        ipm_id,
        distance,
        size_code
      ) AS group_size,
      FROM FINAL_POS_TABLE
  ),
  NEW_WEIGHT_TABLE AS (
    SELECT *,
      CASE
        WHEN (weight >= 0.2) AND (group_size >= 5) THEN 0.2
        WHEN MAX(weight) OVER(
          PARTITION BY zip_code,
          zip3,
          mfg_name,
          ipm_id,
          distance,
          size_code
        ) >= 0.2
        AND (group_size >= 5) THEN (1 / group_size)
        ELSE weight
      END AS new_weight
    FROM WEIGHT_TABLE
  ),
  FINAL_NORMALIZED_TABLE AS (
    SELECT *,
      new_weight / (
        SUM(new_weight) OVER (
          PARTITION BY zip_code,
          zip3,
          mfg_name,
          ipm_id,
          distance,
          size_code
        )
      ) AS new_weight_normalized,
      (
        new_weight / (
          SUM(new_weight) OVER (
            PARTITION BY zip_code,
            zip3,
            mfg_name,
            ipm_id,
            distance,
            size_code
          )
        )
      ) * volume_weighted_unit_retail AS unit_retail_norm,
      (
        new_weight / (
          SUM(new_weight) OVER (
            PARTITION BY zip_code,
            zip3,
            mfg_name,
            ipm_id,
            distance,
            size_code
          )
        )
      ) * volume_weighted_margin AS margin_dollar_norm
    FROM NEW_WEIGHT_TABLE
  )
SELECT lookback.lookback_period AS month_period,
       zip_code,
       zip3,
       mfg_name,
       ipm_id,
       distance,
       size_code,
       COUNT(DISTINCT(org_unit_id)) AS count_unique_stores,
       SUM(total_units_sold) AS total_units_sold,
       SUM(unit_retail_norm) AS unit_retail_normalized,
       SUM(margin_dollar_norm) AS margin_dollar_normalized
FROM FINAL_NORMALIZED_TABLE
GROUP BY zip_code,
         zip3,
         mfg_name,
         ipm_id,
         distance,
         size_code
HAVING count_unique_stores >= 5;
END FOR;

CREATE OR REPLACE TABLE #GCP_PROJECT_ID.#CC_DATASET.pos_national_avg_price AS
SELECT *
FROM national_avg_price;

DROP TABLE national_avg_price;
