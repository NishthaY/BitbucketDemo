insert into "ImportData" ( "CompanyId", "ImportDate", "Finalized", "RowNumber", "EmployeeId", "PlanType", "FirstName", "LastName", "CoverageStartDate", "CoverageEndDate", "AnnualSalary", "Carrier", "CoverageTier", "DateOfBirth", "MonthlyCost", "EmploymentActive", "EmploymentEnd", "EmploymentStart", "MiddleName", "Gender", "Plan", "SSN", "SSNDisplay", "TobaccoUser", "Volume", "Relationship", "Reason", "Address1", "Address2", "City", "State", "PostalCode", "Phone1", "Phone2", "Email1", "Email2","Suffix", "Division", "Department", "BusinessUnit", "OriginalEffectiveDate", "Policy", "GroupNumber", "EnrollmentState", "EmployeeSSN", "EmployeeSSNDisplay", "PolicyRole" )
select
    "ImportData"."CompanyId"
     , "ImportData"."ImportDate"
     , "ImportData"."Finalized"
     , "ImportData"."RowNumber"
     , "ImportData"."EmployeeId"
     , ? as "PlanType" --"ImportData"."PlanType"
     , "ImportData"."FirstName"
     , "ImportData"."LastName"
     , "ImportData"."CoverageStartDate"
     , "ImportData"."CoverageEndDate"
     , "ImportData"."AnnualSalary"
     , ? as "Carrier" -- "ImportData"."Carrier"
     , "ImportData"."CoverageTier"
     , "ImportData"."DateOfBirth"
     , ? as "MonthlyCost" --"ImportData"."MonthlyCost"
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
     , "ImportData"."GroupNumber"
     , "ImportData"."EnrollmentState"
     , "ImportData"."EmployeeSSN"
     , "ImportData"."EmployeeSSNDisplay"
     , "ImportData"."PolicyRole"
from
    "ImportData"
        join "RelationshipData" on ("RelationshipData"."ImportDataId" = "ImportData"."Id" )
        join "CompanyCarrier" on
        (
                    "CompanyCarrier"."CompanyId" = "ImportData"."CompanyId"
                and "CompanyCarrier"."CarrierNormalized" = upper("ImportData"."Carrier")
            )
        join "CompanyPlanType" on
        (
                    "CompanyPlanType"."CarrierId" =  "CompanyCarrier"."Id"
                and "CompanyPlanType"."PlanTypeNormalized" = upper("ImportData"."PlanType")
            )
where
        "ImportData"."CompanyId" = ?
  and "ImportData"."ImportDate" = ?
  and "RelationshipData"."RelationshipCode" = 'employee'
  and "CompanyCarrier"."Id" = ?
  and "CompanyPlanType"."Id" = ?
