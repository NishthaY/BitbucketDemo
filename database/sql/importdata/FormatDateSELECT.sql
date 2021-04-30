select
  TO_CHAR('{DATE}'::date + interval '0 month', 'Month YYYY') as "MonthYYYY"
  , TO_CHAR('{DATE}'::date + interval '0 month', 'mm/dd/yyyy') as "MMDDYYYY"
  , TO_CHAR('{DATE}'::date, 'Month' ) as "Month"
  , TO_CHAR('{DATE}'::date, 'Mon' ) as "Mon"
  , TO_CHAR('{DATE}'::date, 'Mon YYYY' ) as "MonYYYY"
  , TO_CHAR('{DATE}'::date, 'YYYYmm' ) as "YYYYmm"