select
	case
		when "TobaccoIgnored" = true then true
		when "TobaccoIgnored" = false then false
		else false
	end as "TobaccoIgnored"
 from
	"CompanyCoverageTier"
where
    "CompanyId" = ?
	and "Id" = ?
