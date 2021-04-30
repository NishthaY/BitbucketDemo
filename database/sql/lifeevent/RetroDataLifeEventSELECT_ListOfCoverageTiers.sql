select string_agg("UserDescription", ', ') as "CoverageTiers" from
(
	select "UserDescription" from "CompanyCoverageTier" where "Id" in ( {LIST} ) order by "CompanyCoverageTier"."UserDescription" asc
) x
