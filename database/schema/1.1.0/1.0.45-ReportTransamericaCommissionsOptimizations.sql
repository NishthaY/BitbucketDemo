\set db advice2pay

CREATE INDEX ReportTransamericaCommissionDetail_three_index ON public."ReportTransamericaCommissionDetail" ("ImportDataId", "CompanyId", "ImportDate");
CREATE INDEX ReportTransamericaCommission_Id_index ON public."ReportTransamericaCommission" ("Id");