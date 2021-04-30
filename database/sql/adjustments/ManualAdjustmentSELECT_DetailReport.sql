select
    null as "UEID"
     , null as "Division"
     , null as "Department"
     , null as "BusinessUnit"
     , null as "EmployeeId"
     , null as "EmployeeSSN"
     , null as "PersonalSSN"
     , null as "Relationship"
     , null as "FirstName"
     , null as "MiddleName"
     , null as "LastName"
     , null as "Suffix"
     , null as "Address1"
     , null as "Address2"
     , null as "City"
     , null as "State"
     , null as "PostalCode"
     , null as "Phone1"
     , null as "Phone2"
     , null as "Email1"
     , null as "Email2"
     , null as "DateOfBirth"
     , null as "Age"
     , null as "Gender"
     , null as "TobaccoUser"
     , "CompanyCarrier"."UserDescription" as "Carrier"
     , "CompanyPlanType"."UserDescription" as "PlanType"
     , "CompanyPlan"."UserDescription" as "Plan"
     , "CompanyCoverageTier"."UserDescription" as "CoverageTier"
     , null as "CoverageStartDate"
     , null as "CoverageEndDate"
     , null as "Volume"
     , "ManualAdjustment"."Amount" as "MonthlyCost"
     , null as "AnnualSalary"
     , null as "EmploymentActive"
     , null as "EmploymentStart"
     , null as "EmploymentEnd"
     , null as "Reason"
     , null as "Policy"
     , null as "GroupNumber"
     , null as "EnrollmentState"
     , null as "PolicyRole"
     , "ManualAdjustment"."ImportDate" as "ReportDate"
     , 'Manual Adjustment' as "RecordType"
     , "ManualAdjustment"."Memo" as "Memo"
from
    "ManualAdjustment"
        join "CompanyCarrier" on ( "CompanyCarrier"."Id" = "ManualAdjustment"."CarrierId" )
        left join "CompanyPlanType" on ( "CompanyPlanType"."Id" = "ManualAdjustment"."PlanTypeId" )
        left join "CompanyPlan" on ( "CompanyPlan"."Id" = "ManualAdjustment"."PlanId" )
        left join "CompanyCoverageTier" on ( "CompanyCoverageTier"."Id" = "ManualAdjustment"."CoverageTierId" )
where
        "ManualAdjustment"."CompanyId" = ?
  and "ManualAdjustment"."ImportDate" = ?
  and "ManualAdjustment"."CarrierId" = ?
order by
    "CompanyCarrier"."UserDescription" asc
       , "CompanyPlanType"."UserDescription" asc
       , "CompanyPlan"."UserDescription" asc
       , "CompanyCoverageTier"."UserDescription" asc
       , "ManualAdjustment"."Id" asc
