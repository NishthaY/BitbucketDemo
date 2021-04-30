select max("LifeId") as "Max" from "LifeData" where "CompanyId" = ? and "ImportDate" = to_date(?, 'MM/DD/YYYY') - '1month'::interval
