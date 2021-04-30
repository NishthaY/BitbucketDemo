insert into "CompanyLifeDiff" ( "CompanyId", "ImportDate", "LifeId", "EmployeeId" )
select
	"LifeData"."CompanyId"
	, "LifeData"."ImportDate"
	, "LifeData"."LifeId"
	, "ImportData"."EmployeeId"
from
	"LifeData"
	join "ImportData" on ( "ImportData"."Id" = "LifeData"."ImportDataId" )
WHERE
	"LifeData"."CompanyId" = 2
	and "LifeData"."ImportDate" = '09/01/2016'
	and "LifeData"."NewLifeFlg" = true
	and "LifeData"."EIDExistedLastMonthFlg" = false


    -- Not 100% correct yet.  You need to know if there is a life
    -- missing from last month or not.
