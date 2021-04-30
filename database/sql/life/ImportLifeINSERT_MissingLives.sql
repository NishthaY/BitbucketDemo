insert into "ImportData" ( "CompanyId", "ImportDate", "Finalized", "RowNumber", "EmployeeId", "PlanType", "FirstName", "LastName", "CoverageStartDate", "CoverageEndDate", "AnnualSalary", "Carrier", "CoverageTier", "DateOfBirth", "MonthlyCost", "EmploymentActive", "EmploymentEnd", "EmploymentStart", "MiddleName", "Gender", "Plan", "SSN", "SSNDisplay", "TobaccoUser", "Volume", "Relationship", "Reason", "Address1", "Address2", "City", "State", "PostalCode", "Phone1", "Phone2", "Email1", "Email2","Suffix", "Division", "Department", "BusinessUnit", "OriginalEffectiveDate", "Policy" )
  select
    "ImportData"."CompanyId"
    , "ImportLife"."ImportDate" + interval '1 month' as "ImportDate"
    , false as "Finalized"
    , concat('LostLife-', to_char("ImportData"."ImportDate", 'YYYYMMDD'), '-', "ImportData"."RowNumber") as "RowNumber"
    , "ImportData"."EmployeeId"
    , "ImportData"."PlanType"
    , "ImportData"."FirstName"
    , "ImportData"."LastName"
    , "ImportData"."CoverageStartDate"
    , to_char("ImportData"."ImportDate" + interval '-1 day', 'YYYY-MM-DD')::date as "CoverageEndDate"
    , "ImportData"."AnnualSalary"
    , "ImportData"."Carrier"
    , "ImportData"."CoverageTier"
    , "ImportData"."DateOfBirth"
    , "ImportData"."MonthlyCost"
    , "ImportData"."EmploymentActive"
    , "ImportData"."EmploymentEnd"
    , "ImportData"."EmploymentStart"
    , "ImportData"."MiddleName"
    , "ImportData"."Gender"
    , "ImportData"."Plan"
    , "ImportData"."SSN"
    , "ImportData"."SSNDisplay"
    , "ImportData"."TobaccoUser"
    , "ImportData"."Volume"
    , "ImportData"."Relationship"
    , "ImportData"."Reason"
    , "ImportData"."Address1"
    , "ImportData"."Address2"
    , "ImportData"."City"
    , "ImportData"."State"
    , "ImportData"."PostalCode"
    , "ImportData"."Phone1"
    , "ImportData"."Phone2"
    , "ImportData"."Email1"
    , "ImportData"."Email2"
    , "ImportData"."Suffix"
    , "ImportData"."Division"
    , "ImportData"."Department"
    , "ImportData"."BusinessUnit"
    , "ImportData"."OriginalEffectiveDate"
    , "ImportData"."Policy"
  from
    "ImportData"
    join "ImportLife" on ("ImportLife"."ImportDataId" = "ImportData"."Id")
    left join "ImportLife" missing on
                                     (
                                       missing."LifeKey" = "ImportLife"."LifeKey"
                                       and missing."CompanyId" = "ImportLife"."CompanyId"
                                       and missing."ImportDate" = "ImportLife"."ImportDate" + interval '1 month'
                                       )
  WHERE
    "ImportData"."CompanyId" = ?
    and "ImportData"."ImportDate" = ? -- Last Month
    and missing."ImportDataId" is null


