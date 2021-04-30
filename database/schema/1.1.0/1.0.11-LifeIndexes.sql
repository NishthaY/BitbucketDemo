\set db advice2pay


-- LifeData Indexes
create index lifedata_companyid_importdate_lifeid_index
  on "LifeData" ("CompanyId", "ImportDate", "LifeId")
;


-- CompanyLife Indexes
create index companylife_companyid_lifekey_index
  on "CompanyLife" ("CompanyId", "LifeKey")
;





