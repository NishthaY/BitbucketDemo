\set db advice2pay



ALTER TABLE public."UserAcl" ADD "Target" TEXT NULL;
ALTER TABLE public."UserAcl" ADD "TargetId" INTEGER NULL;


create table "UserResponsibleForCompany"
(
  "UserId" integer not null,
  "ParentCompanyId" integer not null,
  "CompanyId" integer not null
)
;
ALTER TABLE "UserResponsibleForCompany" OWNER TO :db;