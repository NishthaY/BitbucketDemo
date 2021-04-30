select
    coalesce(d."EmployeeId", "CompanyUniversalEmployee"."UniversalEmployeeId") as "UEID"
  , cc."CompanyId"
  , cc."ImportDate"
  , carrier."UserDescription" as "Carrier"
  , plantype."UserDescription" as "PlanType"
  , plan."UserDescription" as "Plan"
  , d."DateOfBirth"
  , d."FirstName"
  , d."MiddleName"
  , d."LastName"
  , d."EmployeeId"
  , d."EmployeeSSN"
  , d."SSN" as "PersonSSN"
  , cc."CommissionEffectiveDate"
  , cc."CommissionablePremium"
from
  "CompanyCommission" cc
  join "CompanyCommissionLife" ccl on ( ccl."CompanyId" = cc."CompanyId" and ccl."ImportDate" = cc."ImportDate" and ccl."LifeId" = cc."LifeId" and ccl."CarrierId" = cc."CarrierId" and ccl."PlanTypeId" = cc."PlanTypeId" and ccl."PlanId" = cc."PlanId" )
  left join "ImportData" d on ( d."Id" = ccl."ImportDataId" )
  join "CompanyCarrier" carrier on ( carrier."Id" = cc."CarrierId" )
  join "CompanyPlanType" plantype on ( plantype."Id" = cc."PlanTypeId" )
  join "CompanyPlan" plan on ( plan."Id" = cc."PlanId" )
  left join "CompanyUniversalEmployee" on ( "CompanyUniversalEmployee"."CompanyId" = d."CompanyId" and "CompanyUniversalEmployee"."EmployeeSSN" = d."EmployeeSSN" )
where
  cc."CompanyId" = ?
  and cc."ImportDate" = ?
  and cc."CarrierId" = ?
  and cc."CommissionablePremium" <> 0
order by carrier."UserDescription", plantype."UserDescription", plan."UserDescription", cc."LifeId", cc."CommissionEffectiveDate" asc

