select
	TO_CHAR(TO_DATE(?, 'mm/dd/yyyy'), 'Month YYYY') as "UploadDisplayMonth"
	, TO_CHAR(TO_DATE(?, 'mm/dd/yyyy'), 'mm/dd/yyyy') as "UploadMonth"
    , TO_CHAR(TO_DATE(?, 'mm/dd/yyyy'), 'Mon YYYY') as "UploadDisplayMonthShort"
