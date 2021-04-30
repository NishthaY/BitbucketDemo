update "RelationshipData" set
  "MonthlyCost" = 0
  , "Volume" = 0
  , "Memo" = 'Cost and Volume set to zero dollars based on user elected relationship pricing model.'
where
  "RelationshipData"."CompanyId" = ?
  and "RelationshipData"."ImportDate" = ?
  and "RelationshipData"."RelationshipCode" <> 'employee'
