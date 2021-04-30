SELECT
    "Name" as name
    ,"Display" as display
    , *
FROM
    "PlanTypes"
where
    "Name" not like '%_aso'
    and "Name" not like '%_stoploss'
ORDER BY
    "Display" asc
