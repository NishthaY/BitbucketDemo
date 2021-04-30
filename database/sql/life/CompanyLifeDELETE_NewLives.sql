delete from "CompanyLife"
using "LifeData"
where
	"LifeData"."LifeId" = "CompanyLife"."Id"
	and "LifeData"."CompanyId" = ?
	and "LifeData"."ImportDate" = ?
	and "LifeData"."NewLifeFlg" = true
