insert into "ReportReviewWarnings" ( "CompanyId", "ImportDate", "ImportDataId", "Issue", "Confirm" )
values (
  ?
  , ?
  , 0
  , format('%s not generated. %s', ?, ?)
  , ?
)
