select
    ws."Id" as "StateId"
     , ws."Name" as "StateName"
     , case when wp."Complete" is null then false else wp."Complete" end as "Complete"
     , case when wp."Running" is null then false else wp."Running" end as "Running"
     , case when wp."Waiting" is null then false else wp."Waiting" end as "Waiting"
from
    "WorkflowState" ws
        join "Workflow" w on ( w."Id" = ws."WorkflowId")
        join "WorkflowStateOrder" wo on ( wo."WorkflowStateId" = ws."Id")
        left join "WorkflowProgress" wp on ( wp."WorkflowId" = w."Id" and wp."WorkflowStateId" = ws."Id" and wp."Identifier" = ? and wp."IdentifierType" = ?)
where
        w."Id" = ?
order by wo."SortOrder" asc