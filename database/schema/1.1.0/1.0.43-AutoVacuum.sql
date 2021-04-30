\set db advice2pay

-- Trying to up the agressivness of auto-vacuum.
-- the following alter statments were generated with this.

--select
--  format('ALTER TABLE "%s" set (autovacuum_vacuum_threshold = 50);', relname)
--  , format('ALTER TABLE "%s" set (autovacuum_vacuum_scale_factor = 0.2);', relname)
--from pg_stat_user_tables;


ALTER TABLE "CompanyParentFileTransfer" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyParentFileTransfer" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyPlanType" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyPlanType" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ImportLife" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ImportLife" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LifeEventCompare" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LifeEventCompare" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportTransamericaCommissionDetail" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportTransamericaCommissionDetail" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "MappingColumnHeaders" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "MappingColumnHeaders" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "UserResponsibleForCompany" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "UserResponsibleForCompany" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "SummaryDataYTD" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "SummaryDataYTD" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "BackgroundTask" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "BackgroundTask" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "HistoryFailedJob" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "HistoryFailedJob" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "RetroRules" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "RetroRules" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "RetroData" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "RetroData" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyLifeCompare" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyLifeCompare" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "HistoryEmail" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "HistoryEmail" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "SummaryDataPremiumEquivalent" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "SummaryDataPremiumEquivalent" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AclAction" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AclAction" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LifeOriginalEffectiveDateCompare" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LifeOriginalEffectiveDateCompare" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportTransamericaEligibilityDetails" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportTransamericaEligibilityDetails" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AutomaticAdjustment" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AutomaticAdjustment" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "UserAcl" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "UserAcl" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LifeOriginalEffectiveDate" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LifeOriginalEffectiveDate" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Acl" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Acl" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "MappingColumns" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "MappingColumns" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyFeature" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyFeature" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Company" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Company" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyRelationship" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyRelationship" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ImportData" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ImportData" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "RelationshipMapping" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "RelationshipMapping" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AdjustmentType" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AdjustmentType" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Audit" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Audit" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Age" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Age" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Feature" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Feature" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LogTimer" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LogTimer" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ci_sessions" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ci_sessions" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "SchemaChangeLog" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "SchemaChangeLog" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ProcessQueue" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ProcessQueue" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportTransamericaCommission" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportTransamericaCommission" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Log" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Log" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LifeData" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LifeData" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "WashedData" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "WashedData" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportProperties" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportProperties" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Login" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Login" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AclActionRelationship" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AclActionRelationship" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LogTimerRelationship" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LogTimerRelationship" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyLife" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyLife" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "User" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "User" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Relationship" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Relationship" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ObjectMapping" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ObjectMapping" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AgeBand" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AgeBand" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyCarrier" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyCarrier" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyPlan" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyPlan" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "RelationshipData" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "RelationshipData" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "RetroDataLifeEvent" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "RetroDataLifeEvent" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyMappingColumn" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyMappingColumn" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyParent" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyParent" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "UserPreference" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "UserPreference" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "PlanTypes" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "PlanTypes" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Wizard" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Wizard" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "HistoryChangeToCompany" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "HistoryChangeToCompany" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportType" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportType" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ObjectMappingProperty" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ObjectMappingProperty" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportTransamericaActuarial" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportTransamericaActuarial" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyBestMappedColumn" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyBestMappedColumn" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ImportDataDuplicateLives" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ImportDataDuplicateLives" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyParentPreference" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyParentPreference" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyReport" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyReport" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyLifeDiff" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyLifeDiff" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyPreference" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyPreference" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportTransamericaActuarialDetails" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportTransamericaActuarialDetails" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AgeType" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AgeType" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Carrier" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Carrier" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "Verbiage" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "Verbiage" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "UserCompany" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "UserCompany" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyCoverageTier" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyCoverageTier" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "AppOption" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "AppOption" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "WashRules" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "WashRules" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyFileTransfer" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyFileTransfer" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyParentFeature" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyParentFeature" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ValidationErrors" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ValidationErrors" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "UserCompanyParentRelationship" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "UserCompanyParentRelationship" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportReviewWarnings" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportReviewWarnings" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ReportTransamericaEligibility" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ReportTransamericaEligibility" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "ManualAdjustment" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "ManualAdjustment" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "FileTransferProtocol" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "FileTransferProtocol" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "LifeOriginalEffectiveDateRollback" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "LifeOriginalEffectiveDateRollback" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CarrierMapping" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CarrierMapping" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyLifeResearch" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyLifeResearch" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "SummaryData" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "SummaryData" set (autovacuum_vacuum_scale_factor = 0.2);
ALTER TABLE "CompanyParentCompanyRelationship" set (autovacuum_vacuum_threshold = 50);	ALTER TABLE "CompanyParentCompanyRelationship" set (autovacuum_vacuum_scale_factor = 0.2);
