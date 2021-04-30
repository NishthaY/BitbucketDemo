\set db advice2pay

-- Make this feature a "parent override" feature.
update "Feature" set "CompanyParentFlg" = true where "Code" = 'BENEFICIARY_MAPPING'