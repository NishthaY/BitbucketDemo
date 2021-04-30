select
    count(*) as "SkipsInWindow"
from
     "SkipMonthProcessing"
where
    "CompanyId" = ?
    and "ImportDate" BETWEEN '{IMPORTDATE}'::date - '{MAX} month'::interval AND '{IMPORTDATE}'