update "CompanyParentFeature" set "Enabled" = ? where "CompanyParentId" = ? and "FeatureCode" = ? and ( "Target" = ? OR "Target" is null )