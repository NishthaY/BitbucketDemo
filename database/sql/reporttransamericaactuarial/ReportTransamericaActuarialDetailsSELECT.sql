select
  "PolicyNumber"
  , "GroupNumber"
  , "ResidentState"
  , "StatusCode"
  , "IssueDate"
  , "PaidToDate"
  , "SystemTerminationDate"
  , "BillingMode"
  , "ModalPremium"
  , "InsuredDOB"
  , "InsuredSex"
  , "InsuredState"
  , "InsuredZIP"
  , "InsuredSSN"
  , "InsuredFirstName"
  , "InsuredLastName"
  , "ProductType"
  , "Tier"
  , "Option"
  , "EmployeeSSN"
from
  "ReportTransamericaActuarialDetails"
where
  "CompanyId" = ?
  and "ImportDate" = ?
order by "PolicyNumber", "ProductType", "Option", "Tier", "ModalPremium"