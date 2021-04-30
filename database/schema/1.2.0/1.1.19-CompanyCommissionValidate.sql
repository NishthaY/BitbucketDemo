\set db advice2pay

create table "CompanyCommissionValidate"
(
  "Id" serial not null
    constraint "CompanyCommissionValidate_pkey"
    primary key,
  "CompanyId" integer not null,
  "ImportDate" date not null,
  "LifeId" integer not null,
  "CarrierId" integer not null,
  "PlanTypeId" integer not null,
  "PlanId" integer not null,
  "CommissionablePremium" numeric(18,4),
  "MonthlyCost" numeric(18,4),
  "Validated" boolean default false not null
);
create index companycommissionvalidate_companyid_importdate_lifeid_carrierid on "CompanyCommissionValidate" ("CompanyId", "ImportDate", "LifeId", "CarrierId", "PlanTypeId", "PlanId") ;
create index companycommissionvalidate_companyid_importdate_index on "CompanyCommissionValidate" ("CompanyId", "ImportDate");