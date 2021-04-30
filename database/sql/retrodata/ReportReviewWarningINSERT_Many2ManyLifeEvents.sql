insert into "ReportReviewWarnings" ( "CompanyId", "ImportDate", "ImportDataId", "Issue" )
values (  ?, ?, ?,
    'Many-to-many retro change in {CARRIER} {TIERS} was unable to cross-apply updated coverage start dates from data provided. Charges may require manual adjustment if further adjustments were intended on these records.'
)
