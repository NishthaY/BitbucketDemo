select
    wp."Identifier"
     , wp."IdentifierType"
     , case when cp."Id" is null then c."CompanyName" else cp."Name" end as "IdentifierName"
     , w."Description" as "WorkflowDescriptin"
     , ws."Description" as "StepDescription"
     , p."Value" as "RecentActivity"

from
    "WorkflowProgress" wp
        join "Workflow" w on ( w."Id" = wp."WorkflowId")
        join "WorkflowState" ws on ( ws."Id" = wp."WorkflowStateId" )
        join "WorkflowProgressProperty" p on ( p."Name" = 'recent_activity' and p."Identifier" = wp."Identifier" and p."IdentifierType" = wp."IdentifierType")
        left join "Company" c on ( c."Id" = wp."Identifier" and wp."IdentifierType" = 'company')
        left join "CompanyParent" cp on ( cp."Id" = wp."Identifier" and wp."IdentifierType" = 'companyparent')
where
        wp."Complete" <> true