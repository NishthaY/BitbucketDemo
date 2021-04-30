insert into "CompanyCoverageTier" ( "CompanyId", "CarrierId", "PlanTypeId", "PlanId", "CoverageTierNormalized", "UserDescription", "AgeBandIgnored", "TobaccoIgnored" )
values ( ?, ?, ?, ?, upper(?), ?, ?, ? )
