select to_char(to_date(?, 'MM/DD/YYYY') - interval '1 month' * ?, 'MM/DD/YYYY') as "TargetMonth"
