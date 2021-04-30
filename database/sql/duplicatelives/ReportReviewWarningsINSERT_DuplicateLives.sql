insert into "ReportReviewWarnings" ( "CompanyId", "ImportDate", "ImportDataId", "Issue" )
values (  ?, ?, 0,
        'We found inconsistent or duplicate records per life and coverage in the import file.  Please review <u><a href="{DOWNLOAD_LINK}">these records</a></u> and remove duplicate entries or check for consistency in name fields.'
)
