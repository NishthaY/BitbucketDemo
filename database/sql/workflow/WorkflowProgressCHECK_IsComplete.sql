select
    case when wp."Complete" then true else false end as "Complete"
from
    "WorkflowProgress" wp
    join "Workflow" w on ( w."Id" = wp."WorkflowId" )
    join "WorkflowState" ws on ( ws."Id" = wp."WorkflowStateId" )
    join "WorkflowStateOrder" wo on ( wo."WorkflowId" = w."Id" and wo."WorkflowStateId" = ws."Id" )
where
    wp."Identifier" = ?
    and wp."IdentifierType" = ?
    and wp."WorkflowId" = ?
order by
    wo."SortOrder" desc
limit 1
